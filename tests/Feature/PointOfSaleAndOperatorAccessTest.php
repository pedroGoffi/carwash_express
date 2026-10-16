<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Services\Operator\TodayOrderSummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PointOfSaleAndOperatorAccessTest extends TestCase
{
    use RefreshDatabase;

    private function companyAndOperator(string $document): array
    {
        $company = Company::create(['legal_name' => 'Empresa '.$document, 'document' => $document]);
        $operator = User::create(['company_id' => $company->id, 'name' => 'Operador '.$document, 'role' => 'operator', 'password' => '12345678']);
        return [$company, $operator];
    }

    private function product(Company $company, string $name, string $type, int $price): Product
    {
        $product = Product::create(['company_id' => $company->id, 'name' => $name, 'type' => $type, 'active' => true]);
        ProductPrice::create(['product_id' => $product->id, 'vehicle_type' => 'normal', 'price' => $price]);
        return $product;
    }

    public function test_operator_can_sell_drinks_without_wash_vehicle_or_plate_and_not_create_vehicle_delivery(): void
    {
        [$company, $operator] = $this->companyAndOperator('A001');
        $drink = $this->product($company, 'Água mineral', 'drink', 5);
        $snack = $this->product($company, 'Salgado', 'snack', 9);

        $this->actingAs($operator)->post(route('operator.orders.store'), [
            'order_type' => 'sale', 'customer_type' => 'pf',
            'items' => [$drink->id => 3, $snack->id => 1],
            'payments' => json_encode([['method' => 'pix', 'amount' => 24.0]]),
        ])->assertRedirect(route('operator.orders.index'));

        $order = ServiceOrder::firstOrFail();
        $this->assertSame('sale', $order->order_type);
        $this->assertNull($order->license_plate);
        $this->assertSame('delivered', $order->status->value);
        $this->assertNotNull($order->delivered_at);
        $this->assertEquals(24, (float) $order->total);
        $this->assertSame(0, DB::table('service_order_items')->where('is_primary_wash', true)->count());
        $this->assertSame(0, DB::table('service_orders')->where('status', 'finished')->count());

        $summary = app(TodayOrderSummaryService::class)->forCompany($company->id);
        $this->assertSame(0, $summary['orders']);
        $this->assertSame(0, $summary['vehicles']['normal']);
        $this->assertSame(1, $summary['sales']['orders']);
        $this->assertEquals(24, $summary['sales']['amount']);
        $this->assertSame(3, $summary['drinks']['quantity']);
        $this->assertEquals(24, $summary['received']);

        $this->actingAs($operator)->get(route('operator.orders.receipt', $order))
            ->assertOk()->assertSee('VENDA BALCAO / PDV')->assertSee('Água mineral');
        $this->actingAs($operator)->get(route('operator.services.to-deliver'))->assertOk()->assertDontSee($order->code);
    }

    public function test_wash_requires_plate_and_foreign_company_products_are_rejected(): void
    {
        [$company, $operator] = $this->companyAndOperator('A002');
        [$other] = $this->companyAndOperator('A003');
        $wash = $this->product($company, 'Lavagem', 'wash', 40);
        $foreign = $this->product($other, 'Produto externo', 'drink', 6);

        $this->actingAs($operator)->post(route('operator.orders.store'), [
            'order_type' => 'sale', 'customer_type' => 'pf', 'items' => [$wash->id => 1],
        ])->assertSessionHasErrors('license_plate');
        $this->actingAs($operator)->post(route('operator.orders.store'), [
            'order_type' => 'sale', 'customer_type' => 'pf', 'items' => [$foreign->id => 1],
        ])->assertSessionHasErrors('items');
        $this->assertDatabaseCount('service_orders', 0);
    }


    public function test_unified_registration_auto_detects_sale_without_mode_and_keeps_register_open(): void
    {
        [$company, $operator] = $this->companyAndOperator('A005');
        $drink = $this->product($company, 'Refrigerante', 'drink', 8);

        $this->actingAs($operator)->post(route('operator.orders.store'), [
            'customer_type' => 'pf', 'vehicle_type' => 'pickup', 'after_save' => 'new',
            'items' => [$drink->id => 2],
            'payments' => json_encode([['method' => 'cash', 'amount' => 16]]),
        ])->assertRedirect(route('operator.orders.create'))
          ->assertSessionHas('created_order_id');

        $order = ServiceOrder::firstOrFail();
        $this->assertSame('sale', $order->order_type);
        $this->assertSame('normal', $order->vehicle_type);
        $this->assertNull($order->license_plate);
        $this->assertSame('delivered', $order->status->value);
        $this->assertEquals(16, (float) $order->total);
    }

    public function test_mixed_wash_and_drinks_auto_detects_service_even_with_stale_sale_mode(): void
    {
        [$company, $operator] = $this->companyAndOperator('A006');
        $wash = $this->product($company, 'Lavagem rápida', 'wash', 30);
        $drink = $this->product($company, 'Água', 'drink', 5);

        $this->actingAs($operator)->post(route('operator.orders.store'), [
            'order_type' => 'sale', // Informação do navegador não pode reclassificar os itens.
            'customer_type' => 'pf', 'license_plate' => 'ABC1D23',
            'vehicle_type' => 'normal', 'initial_status' => 'waiting',
            'primary_wash_id' => $wash->id,
            'items' => [$wash->id => 1, $drink->id => 2],
        ])->assertRedirect(route('operator.orders.index'));

        $order = ServiceOrder::firstOrFail();
        $this->assertSame('service', $order->order_type);
        $this->assertSame('ABC1D23', $order->license_plate);
        $this->assertSame('waiting', $order->status->value);
        $this->assertEquals(40, (float) $order->total);
        $this->assertSame(1, DB::table('service_order_items')->where('service_order_id', $order->id)->where('is_primary_wash', true)->count());
        $summary = app(TodayOrderSummaryService::class)->forCompany($company->id);
        $this->assertSame(1, $summary['orders']);
        $this->assertSame(0, $summary['sales']['orders']);
        $this->assertSame(2, $summary['drinks']['quantity']);
    }

    public function test_pdv_items_remain_sale_when_legacy_client_sends_service_type(): void
    {
        [$company, $operator] = $this->companyAndOperator('A007');
        $drink = $this->product($company, 'Suco', 'drink', 6);

        $this->actingAs($operator)->post(route('operator.orders.store'), [
            'order_type' => 'service', 'customer_type' => 'pf',
            'items' => [$drink->id => 1],
        ])->assertRedirect(route('operator.orders.index'));
        $this->assertSame('sale', ServiceOrder::firstOrFail()->order_type);
    }

    public function test_operator_can_access_products_team_payroll_and_all_financial_entries_but_not_close_payroll(): void
    {
        [$company, $operator] = $this->companyAndOperator('A004');
        $product = $this->product($company, 'Café', 'drink', 7);

        $this->actingAs($operator)->get(route('operator.orders.create'))
            ->assertOk()->assertSee('Novo lançamento')->assertSee('Bebidas e produtos')->assertSee('Lavagem principal')
            ->assertDontSee('data-order-mode');
        $this->actingAs($operator)->get(route('manager.products.index'))->assertOk();
        $this->actingAs($operator)->get(route('manager.products.create'))->assertOk();
        $this->actingAs($operator)->get(route('manager.users.index'))->assertOk();
        $this->actingAs($operator)->get(route('manager.payroll.index'))->assertOk();
        $this->actingAs($operator)->get(route('manager.operating-expenses.index'))->assertOk();

        $this->actingAs($operator)->post(route('manager.advances.store'), [
            'user_id' => $operator->id, 'amount' => 20, 'date' => now()->toDateString(),
        ])->assertRedirect();
        $this->actingAs($operator)->post(route('manager.consumptions.store'), [
            'user_id' => $operator->id, 'product_id' => $product->id, 'quantity' => 1, 'date' => now()->toDateString(),
        ])->assertRedirect();
        $this->actingAs($operator)->post(route('manager.expenses.store'), [
            'user_id' => $operator->id, 'category' => 'meal', 'amount' => 12, 'date' => now()->toDateString(),
        ])->assertRedirect();
        $this->actingAs($operator)->post(route('manager.operating-expenses.store'), [
            'category' => 'utilities', 'amount' => 30, 'date' => now()->toDateString(),
        ])->assertSessionHasErrors('category'); // Invalid category blocked.

        $this->actingAs($operator)->post(route('manager.operating-expenses.store'), [
            'category' => 'electricity', 'amount' => 75, 'date' => now()->toDateString(),
        ])->assertRedirect();
        $this->assertDatabaseHas('operating_expenses', ['company_id' => $company->id, 'category' => 'electricity', 'amount' => 75]);

        $this->actingAs($operator)->post(route('manager.payroll.close-period'), [
            'month' => now()->format('Y-m'),
        ])->assertForbidden();
        $this->actingAs($operator)->get(route('manager.dashboard'))->assertForbidden();
        $this->actingAs($operator)->get(route('manager.reports.index'))->assertForbidden();
    }
}
