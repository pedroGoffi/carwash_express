<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Advance;
use App\Models\EmployeeExpense;
use App\Models\PayrollHistory;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $period = $this->period($request->query('month'));
        $start = $period->toDateString();
        $end = $period->copy()->endOfMonth()->toDateString();
        $tab = in_array($request->query('tab'), ['overview','movements','history'], true) ? $request->query('tab') : 'overview';
        $userId = filter_var($request->query('user_id'), FILTER_VALIDATE_INT) ?: null;
        $team = User::query()->where('company_id', $companyId)->where('role', 'operator')
            ->orderBy('name')->get(['id','name','active']);
        if ($userId && !$team->contains('id', $userId)) abort(404);

        $workers = User::query()
            ->where('company_id', $companyId)->where('role', 'operator')
            ->when(!$userId, fn ($q) => $q->where('active', true))
            ->when($userId, fn ($q) => $q->whereKey($userId))
            ->withSum(['advances as money_advances' => fn ($q) => $q->whereBetween('date', [$start,$end])->where('type','money')], 'amount')
            ->withSum(['advances as product_consumptions' => fn ($q) => $q->whereBetween('date', [$start,$end])->where('type','product')], 'amount')
            ->withSum(['employeeExpenses as extra_costs' => fn ($q) => $q->whereBetween('date', [$start,$end])], 'amount')
            ->orderBy('name')->get()->each(function (User $worker) {
                $worker->base_amount = (float)$worker->daily_rate > 0
                    ? round((float)$worker->daily_rate * (int)$worker->days_worked, 2)
                    : (float)$worker->monthly_salary;
                $worker->money_advances = (float)($worker->money_advances ?? 0);
                $worker->product_consumptions = (float)($worker->product_consumptions ?? 0);
                $worker->extra_costs = (float)($worker->extra_costs ?? 0);
                $worker->net_amount = round($worker->base_amount - $worker->money_advances - $worker->product_consumptions, 2);
                $worker->employer_cost = round($worker->base_amount + $worker->extra_costs, 2);
            });

        $summary = [
            'base' => $workers->sum('base_amount'),
            'advances' => $workers->sum('money_advances'),
            'consumptions' => $workers->sum('product_consumptions'),
            'extras' => $workers->sum('extra_costs'),
            'net' => $workers->sum('net_amount'),
            'employer' => $workers->sum('employer_cost'),
        ];

        // Abre rapidamente: listagens históricas só consultadas quando solicitadas.
        $transactions = null;
        $expenses = null;
        $history = null;
        if ($tab === 'movements') {
            $transactions = Advance::query()->with(['user:id,name','product:id,name'])
                ->where('company_id',$companyId)->whereBetween('date',[$start,$end])
                ->when($userId, fn ($q) => $q->where('user_id',$userId))
                ->orderByDesc('date')->orderByDesc('id')->paginate(15, ['*'], 'advances_page')->withQueryString();
            $expenses = EmployeeExpense::query()->with('user:id,name')->where('company_id',$companyId)
                ->whereBetween('date',[$start,$end])->when($userId,fn ($q) => $q->where('user_id',$userId))
                ->orderByDesc('date')->orderByDesc('id')->paginate(15, ['*'], 'expenses_page')->withQueryString();
        }
        if ($tab === 'history') {
            $history = PayrollHistory::query()->with(['user:id,name','closedBy:id,name'])
                ->where('company_id',$companyId)->when($userId,fn ($q) => $q->where('user_id',$userId))
                ->orderByDesc('period')->orderByDesc('id')->paginate(20)->withQueryString();
        }
        $products = $tab === 'overview'
            ? Product::query()->where('company_id',$companyId)->where('active',true)
                ->whereIn('type',['drink','snack'])->with('prices:id,product_id,vehicle_type,price')
                ->orderBy('name')->get()
            : collect();
        $closedForPeriod = PayrollHistory::where('company_id',$companyId)->where('period',$start)->exists();
        return view('manager.payroll.index', compact(
            'companyId','period','tab','team','userId','workers','summary','transactions','expenses','history','products','closedForPeriod'
        ));
    }

    // Mantém a rota antiga indisponível: salário é cadastrado em /manager/users/{user}/edit.
    public function update(Request $request, User $user)
    {
        abort(404);
    }

    public function closePeriod(Request $request)
    {
        $request->validate(['month' => ['required','date_format:Y-m']]);
        $companyId = auth()->user()->company_id;
        $period = $this->period($request->input('month'));
        $start = $period->toDateString();
        $end = $period->copy()->endOfMonth()->toDateString();
        $workers = User::query()->where('company_id',$companyId)->where('role','operator')->where('active',true)
            ->withSum(['advances as money_advances' => fn ($q) => $q->whereBetween('date',[$start,$end])->where('type','money')], 'amount')
            ->withSum(['advances as product_consumptions' => fn ($q) => $q->whereBetween('date',[$start,$end])->where('type','product')], 'amount')
            ->withSum(['employeeExpenses as extra_costs' => fn ($q) => $q->whereBetween('date',[$start,$end])], 'amount')->get();
        DB::transaction(function () use ($workers,$companyId,$start) {
            foreach ($workers as $worker) {
                $base = (float)$worker->daily_rate > 0
                    ? round((float)$worker->daily_rate * (int)$worker->days_worked,2)
                    : (float)$worker->monthly_salary;
                $advances = (float)($worker->money_advances ?? 0);
                $consumptions = (float)($worker->product_consumptions ?? 0);
                $extras = (float)($worker->extra_costs ?? 0);
                PayrollHistory::updateOrCreate(
                    ['company_id'=>$companyId,'user_id'=>$worker->id,'period'=>$start],
                    ['base_amount'=>$base,'advances_amount'=>$advances,'consumptions_amount'=>$consumptions,
                     'net_amount'=>$base-$advances-$consumptions,'extra_costs_amount'=>$extras,
                     'employer_cost_amount'=>$base+$extras,'days_worked'=>(int)$worker->days_worked,
                     'closed_by'=>auth()->id(),'closed_at'=>now()]
                );
            }
        });
        return redirect()->route('manager.payroll.index',['month'=>substr($start,0,7)])
            ->with('success','Competência registrada no histórico. Despesas extras não são descontos salariais.');
    }

    private function period(?string $value): Carbon
    {
        if (!is_string($value) || !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/',$value)) return now()->startOfMonth();
        return Carbon::createFromFormat('!Y-m',$value)->startOfMonth();
    }
}
