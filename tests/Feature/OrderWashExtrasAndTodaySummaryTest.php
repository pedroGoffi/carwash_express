<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Services\Operator\TodayOrderSummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrderWashExtrasAndTodaySummaryTest extends TestCase
{
    use RefreshDatabase;

    private function company(string $code): array
    {
        $company = Company::create(['legal_name' => 'Empresa '.$code, 'document' => $code]);
        $operator = User::create(['company_id' => $company->id, 'name' => 'Operador', 'role' => 'operator', 'password' => 'secure-test-password']);

        return [$company, $operator];
    }

    private function product(Company $company, string $name, string $type, float $price): Product
    {
        $product = Product::create(['company_id' => $company->id, 'name' => $name, 'type' => $type, 'active' => true]);
        ProductPrice::create(['product_id' => $product->id, 'vehicle_type' => 'normal', 'price' => $price]);
        return $product;
    }

    private function payload(Product $wash, array $items): array
    {
        return [
            'customer_type' => 'pf',
            'license_plate' => 'ABC1D23',
            'vehicle_type' => 'normal',
            'initial_status' => 'finished',
            'primary_wash_id' => $wash->id,
            'items' => $items,
            'payments' => json_encode([['method' => 'pix', 'amount' => 65.00]]),
        ];
    }

    public function test_order_stores_primary_wash_extra_wash_and_drink_as_distinct_lines(): void
    {
        [$company, $user] = $this->company('111');
        $wash = $this->product($company, 'Lavagem completa', 'wash', 50);
        $wax = $this->product($company, 'Aplicação de cera', 'wash', 10);
        $drink = $this->product($company, 'Água mineral', 'drink', 5);

        $this->actingAs($user)->post(route('operator.orders.store'),
            $this->payload($wash, [$wash->id => 1, $wax->id => 1, $drink->id => 1])
        )->assertRedirect(route('operator.orders.index'));

        $order = ServiceOrder::where('company_id', $company->id)->firstOrFail();
        $this->assertEquals(65, (float) $order->total);
        $this->assertEquals(1, DB::table('service_order_items')->where('service_order_id', $order->id)->where('is_primary_wash', true)->count());
        $this->assertDatabaseHas('service_order_items', ['service_order_id' => $order->id, 'product_id' => $wax->id, 'is_primary_wash' => false, 'total_price' => 10]);
        $this->assertDatabaseHas('service_order_items', ['service_order_id' => $order->id, 'product_id' => $drink->id, 'is_primary_wash' => false, 'total_price' => 5]);

        $summary = app(TodayOrderSummaryService::class)->forCompany($company->id);
        $this->assertSame(1, $summary['orders']);
        $this->assertSame(1, $summary['vehicles']['normal']);
        $this->assertSame(1, $summary['wash_types']['quantity']);
        $this->assertSame('Lavagem completa', $summary['wash_types']['items'][0]['name']);
        $this->assertSame(1, $summary['wash_types']['items'][0]['vehicles']['normal']);
        $this->assertSame(0, $summary['wash_types']['items'][0]['vehicles']['pickup']);
        $this->assertEquals(50, $summary['wash_types']['amount']);
        $this->assertSame(1, $summary['wash_addon_types']['items'][0]['vehicles']['normal']);
        $this->assertSame(1, $summary['wash_addons']['quantity']);
        $this->assertEquals(10, $summary['wash_addons']['amount']);
        $this->assertSame(1, $summary['drinks']['quantity']);
        $this->assertEquals(5, $summary['drinks']['amount']);
        $this->assertEquals(65, $summary['received']);
        $this->assertEquals(65, $summary['payments']['pix']['amount']);

        $this->actingAs($user)->get(route('operator.orders.receipt', $order))
            ->assertOk()->assertSee('LAVAGEM COMPLETA')->assertSee('Aplicação de cera');
    }

    public function test_wash_breakdown_crosses_wash_type_and_vehicle_without_counting_addons_twice(): void
    {
        [$company, $user] = $this->company('M001');
        [$otherCompany] = $this->company('M002');
        $complete = $this->product($company, 'Lavagem completa', 'wash', 50);
        $basic = $this->product($company, 'Lavagem simples', 'wash', 30);
        $wax = $this->product($company, 'Cera adicional', 'wash', 15);
        $foreignWash = $this->product($otherCompany, 'Lavagem de outro cliente', 'wash', 95);

        $create = function (int $companyId, int $washId, string $vehicle, int $amount, ?int $addonId = null) {
            $order = ServiceOrder::create([
                'company_id' => $companyId, 'code' => 'TEST'.bin2hex(random_bytes(4)),
                'status' => 'finished', 'vehicle_type' => $vehicle,
                'license_plate' => 'ABC1234', 'total' => $amount,
            ]);
            $items = [
                $washId => ['quantity' => 1, 'unit_price' => $amount - ($addonId ? 15 : 0),
                    'total_price' => $amount - ($addonId ? 15 : 0), 'is_primary_wash' => true],
            ];
            if ($addonId) $items[$addonId] = [
                'quantity' => 1, 'unit_price' => 15, 'total_price' => 15, 'is_primary_wash' => false,
            ];
            $order->products()->attach($items);
            return $order;
        };

        $create($company->id, $complete->id, 'normal', 65, $wax->id);
        $create($company->id, $complete->id, 'suv', 70);
        $create($company->id, $basic->id, 'pickup', 40);
        $create($otherCompany->id, $foreignWash->id, 'normal', 95);
        $cancelled = $create($company->id, $basic->id, 'normal', 30);
        $cancelled->update(['status' => 'cancelled']);

        $summary = app(TodayOrderSummaryService::class)->forCompany($company->id);
        $this->assertSame(3, $summary['orders']);
        $this->assertSame(3, $summary['wash_types']['quantity']);
        $this->assertSame(1, $summary['wash_addon_types']['quantity']);
        $this->assertCount(2, $summary['wash_types']['items']);
        $rows = collect($summary['wash_types']['items'])->keyBy('name');
        $this->assertSame(1, $rows['Lavagem completa']['vehicles']['normal']);
        $this->assertSame(1, $rows['Lavagem completa']['vehicles']['suv']);
        $this->assertSame(0, $rows['Lavagem completa']['vehicles']['pickup']);
        $this->assertSame(1, $rows['Lavagem simples']['vehicles']['pickup']);
        $this->assertEquals(160, $summary['wash_types']['amount']);
        $this->assertEquals(15, $summary['wash_addon_types']['amount']);

        $this->actingAs($user)->getJson(route('operator.orders.today-summary'))
            ->assertOk()->assertJsonPath('wash_types.quantity', 3);
        $this->actingAs($user)->get(route('operator.daily-summary'))
            ->assertOk()->assertSee('Lavagens principais por tipo e veículo')
            ->assertSee('Lavagens adicionais por tipo e veículo');
        $this->actingAs($user)->get(route('operator.orders.create'))
            ->assertOk()->assertSee('Lavagens por tipo e veículo');
    }

    public function test_invalid_primary_and_wash_quantities_are_rejected(): void
    {
        [$company, $user] = $this->company('222');
        $wash = $this->product($company, 'Lavagem', 'wash', 50);
        $drink = $this->product($company, 'Suco', 'drink', 5);

        $this->actingAs($user)->post(route('operator.orders.store'),
            $this->payload($wash, [$drink->id => 1])
        )->assertSessionHasErrors('primary_wash_id');
        $this->actingAs($user)->post(route('operator.orders.store'),
            $this->payload($wash, [$wash->id => 2])
        )->assertSessionHasErrors('items');
        $this->assertDatabaseCount('service_orders', 0);
    }

    public function test_extra_services_cannot_be_ordered_from_a_different_company(): void
    {
        [$company, $user] = $this->company('333');
        [$another] = $this->company('444');
        $wash = $this->product($company, 'Lavagem', 'wash', 50);
        $foreignWash = $this->product($another, 'Lavagem de outra empresa', 'wash', 10);

        $this->actingAs($user)->post(route('operator.orders.store'),
            $this->payload($wash, [$wash->id => 1, $foreignWash->id => 1])
        )->assertSessionHasErrors('items');
        $this->assertDatabaseCount('service_orders', 0);
    }

    public function test_daily_summary_counts_payments_on_payment_date_and_isolates_tenants(): void
    {
        [$company, $user] = $this->company('555');
        [$other] = $this->company('666');
        $old = ServiceOrder::create([
            'company_id' => $company->id, 'code' => 'OS26100001', 'status' => 'delivered',
            'vehicle_type' => 'suv', 'license_plate' => 'OLD1234', 'total' => 80,
        ]);
        $old->forceFill(['created_at' => now()->subDay()])->save();
        Payment::create(['service_order_id' => $old->id, 'method' => 'credit', 'amount' => 40]);

        $foreign = ServiceOrder::create([
            'company_id' => $other->id, 'code' => 'OS26100002', 'status' => 'finished',
            'vehicle_type' => 'pickup', 'license_plate' => 'OTH1234', 'total' => 100,
        ]);
        Payment::create(['service_order_id' => $foreign->id, 'method' => 'cash', 'amount' => 100]);

        $summary = app(TodayOrderSummaryService::class)->forCompany($company->id);
        $this->assertSame(0, $summary['orders']);
        $this->assertEquals(40, $summary['received']);
        $this->assertEquals(40, $summary['payments']['credit']['amount']);
        $this->assertEquals(0, $summary['payments']['cash']['amount']);

        $this->actingAs($user)->getJson(route('operator.orders.today-summary'))
            ->assertOk()->assertJsonPath('received', 40)->assertJsonPath('vehicles.pickup', 0);
    }

    public function test_guest_cannot_access_daily_summary(): void
    {
        $this->get(route('operator.orders.today-summary'))->assertRedirect(route('login'));
    }
}
