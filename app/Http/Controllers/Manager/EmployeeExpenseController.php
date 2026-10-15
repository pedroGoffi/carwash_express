<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\EmployeeExpense;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeExpenseController extends Controller
{
    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $data = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')->where('company_id', $companyId)],
            'category' => ['required', Rule::in(['benefit','transport','meal','tax','bonus','equipment','other'])],
            'amount' => 'required|numeric|min:0.01|max:9999999',
            'date' => 'required|date',
            'description' => 'nullable|string|max:500',
        ]);
        $data['company_id'] = $companyId;
        $data['created_by'] = auth()->id();
        EmployeeExpense::create($data);
        return redirect()->route('manager.payroll.index', [
            'month' => substr($data['date'], 0, 7), 'user_id' => $data['user_id'], 'tab' => 'movements',
        ])->with('success', 'Despesa do colaborador registrada. Não desconta automaticamente do salário.');
    }

    public function destroy(EmployeeExpense $expense)
    {
        abort_unless($expense->company_id === auth()->user()->company_id, 403);
        $month = $expense->date->format('Y-m');
        $userId = $expense->user_id;
        $expense->delete();
        return redirect()->route('manager.payroll.index', ['month' => $month, 'user_id' => $userId, 'tab' => 'movements'])
            ->with('success', 'Despesa removida.');
    }
}
