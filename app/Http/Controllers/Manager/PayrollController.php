<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id;
        
        $workers = User::where('company_id', $companyId)
            ->where('role', 'operator')
            ->with('advances')
            ->get();
            
        foreach ($workers as $worker) {
            $worker->calculatePayroll();
        }
        
        // Totais
        $totalSalary = $workers->sum('monthly_salary');
        $totalAdvances = $workers->sum('total_advances');
        $totalToReceive = $workers->sum('total_to_receive');
        $totalConsumptions = $workers->sum('total_consumptions');
        
        return view('manager.payroll.index', compact('workers', 'totalSalary', 'totalAdvances', 'totalToReceive', 'totalConsumptions'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'monthly_salary' => 'nullable|numeric|min:0',
            'daily_rate' => 'nullable|numeric|min:0',
            'days_worked' => 'nullable|integer|min:0|max:31',
        ]);

        $user->update([
            'monthly_salary' => $request->monthly_salary ?? $user->monthly_salary,
            'daily_rate' => $request->daily_rate ?? $user->daily_rate,
            'days_worked' => $request->days_worked ?? $user->days_worked,
        ]);
        
        $user->calculatePayroll();
        
        return redirect()->route('manager.payroll.index')
            ->with('success', 'Salário atualizado!');
    }
}