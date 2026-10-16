<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\OperatingExpense;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class OperatingExpenseController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate(['month' => ['nullable', 'date_format:Y-m']]);
        $month = $validated['month'] ?? now()->format('Y-m');
        $companyId = (int) $request->user()->company_id;
        $base = OperatingExpense::query()->where('company_id', $companyId)
            ->whereBetween('date', [$month.'-01', \Carbon\CarbonImmutable::createFromFormat('!Y-m', $month)->endOfMonth()->format('Y-m-d')]);

        $total = (float) (clone $base)->sum('amount');
        $byCategory = (clone $base)->select('category')->selectRaw('COUNT(*) AS quantity, SUM(amount) AS total')
            ->groupBy('category')->orderByDesc('total')->get();
        $expenses = (clone $base)->with('creator:id,name')->orderByDesc('date')->orderByDesc('id')
            ->paginate(20)->withQueryString();

        return view('manager.operating-expenses.index', compact('expenses', 'month', 'total', 'byCategory'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => ['required', Rule::in(array_keys(OperatingExpense::CATEGORIES))],
            'description' => ['nullable', 'string', 'max:500'],
            'date' => ['required', 'date_format:Y-m-d'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999.99', 'decimal:0,2'],
        ]);
        $validated['company_id'] = (int) $request->user()->company_id;
        $validated['created_by'] = $request->user()->id;
        OperatingExpense::create($validated);

        return redirect()->route('manager.operating-expenses.index', ['month' => substr($validated['date'], 0, 7)])
            ->with('success', 'Custo operacional registrado.');
    }

    public function destroy(Request $request, OperatingExpense $expense)
    {
        abort_unless((int) $expense->company_id === (int) $request->user()->company_id, 403);
        $month = $expense->date->format('Y-m');
        $expense->delete();
        return redirect()->route('manager.operating-expenses.index', ['month' => $month])
            ->with('success', 'Custo operacional removido.');
    }
}
