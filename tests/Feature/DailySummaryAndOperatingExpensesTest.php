<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\OperatingExpense;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Services\Operator\TodayOrderSummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailySummaryAndOperatingExpensesTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $document, string $role = 'manager'): array
    {
        $company = Company::create(['legal_name' => 'Empresa '.$document, 'document' => $document]);
        $user = User::create(['company_id' => $company->id, 'name' => 'Usuário teste', 'role' => $role, 'password' => 'secure-password']);
        return [$company, $user];
    }

    public function test_daily_summary_date_selector_is_tenant_scoped_and_payments_follow_payment_date(): void
    {
        [$company, $user] = $this->user('D101', 'operator');
        [$other] = $this->user('D102', 'operator');
        $yesterday = now()->subDay()->format('Y-m-d');
        $order = ServiceOrder::create([
            'company_id' => $company->id, 'code' => 'OS26100051', 'status' => 'delivered',
            'vehicle_type' => 'normal', 'license_plate' => 'ABC1234', 'total' => 60,
        ]);
        $order->forceFill(['created_at' => now()->subDay()])->save();
        Payment::create(['service_order_id' => $order->id, 'method' => 'pix', 'amount' => 60]);
        ServiceOrder::create([
            'company_id' => $other->id, 'code' => 'OS26100052', 'status' => 'delivered',
            'vehicle_type' => 'pickup', 'license_plate' => 'CDE1234', 'total' => 90,
        ]);

        $previous = app(TodayOrderSummaryService::class)->forCompany($company->id, $yesterday);
        $this->assertSame(1, $previous['orders']);
        $this->assertSame(0.0, $previous['received']);
        $today = app(TodayOrderSummaryService::class)->forCompany($company->id);
        $this->assertSame(0, $today['orders']);
        $this->assertEquals(60, $today['received']);

        $this->actingAs($user)->get(route('operator.daily-summary', ['date' => $yesterday]))
            ->assertOk()->assertSee('Resumo de')->assertSee('D-1 (ontem)');
        $this->actingAs($user)->getJson(route('operator.orders.today-summary', ['date' => $yesterday]))
            ->assertOk()->assertJsonPath('orders', 1)->assertJsonPath('received', 0);
        $this->actingAs($user)->get(route('operator.daily-summary', ['date' => 'invalid']))
            ->assertSessionHasErrors('date');
    }

    public function test_zero_price_washes_are_not_displayed_in_new_order(): void
    {
        [$company, $user] = $this->user('W101', 'operator');
        $zero = Product::create(['company_id' => $company->id, 'name' => 'Lavagem sem preço', 'type' => 'wash', 'active' => true]);
        ProductPrice::create(['product_id' => $zero->id, 'vehicle_type' => 'normal', 'price' => 0]);
        $paid = Product::create(['company_id' => $company->id, 'name' => 'Lavagem completa', 'type' => 'wash', 'active' => true]);
        ProductPrice::create(['product_id' => $paid->id, 'vehicle_type' => 'normal', 'price' => 50]);
        $this->actingAs($user)->get(route('operator.orders.create'))
            ->assertOk()->assertDontSee('Lavagem sem preço')->assertSee('Lavagem completa');
    }

    public function test_operating_expenses_are_managed_separately_from_employee_expenses_and_tenant_scoped(): void
    {
        [$company, $manager] = $this->user('C101');
        [$other, $otherManager] = $this->user('C102');
        $data = ['category' => 'electricity', 'amount' => '185.50', 'date' => now()->format('Y-m-d'), 'description' => 'Conta de luz'];
        $this->actingAs($manager)->post(route('manager.operating-expenses.store'), $data)
            ->assertRedirect();
        $expense = OperatingExpense::where('company_id', $company->id)->firstOrFail();
        $this->assertDatabaseHas('operating_expenses', ['id' => $expense->id, 'category' => 'electricity', 'amount' => 185.50]);
        $this->assertDatabaseCount('employee_expenses', 0);
        $this->actingAs($otherManager)->get(route('manager.operating-expenses.index'))
            ->assertOk()->assertDontSee('Conta de luz');
        $this->actingAs($otherManager)->delete(route('manager.operating-expenses.destroy', $expense))
            ->assertForbidden();
        $this->actingAs($manager)->get(route('manager.operating-expenses.index'))
            ->assertOk()->assertSee('Conta de luz');
        $this->actingAs($manager)->delete(route('manager.operating-expenses.destroy', $expense))
            ->assertRedirect();
        $this->assertDatabaseMissing('operating_expenses', ['id' => $expense->id]);
    }

    public function test_operator_cannot_create_operating_expenses(): void
    {
        [, $operator] = $this->user('C103', 'operator');
        $this->actingAs($operator)->get(route('manager.operating-expenses.index'))->assertForbidden();
        $this->actingAs($operator)->post(route('manager.operating-expenses.store'), [
            'category' => 'water', 'date' => now()->format('Y-m-d'), 'amount' => 10,
        ])->assertForbidden();
    }
}
