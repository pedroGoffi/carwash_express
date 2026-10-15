<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Advance;
use App\Models\User;
use Illuminate\Http\Request;

class AdvanceController extends Controller
{
    public function index() { return redirect()->route('manager.payroll.index'); }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'reason' => 'nullable|string|max:500',
        ]);
        $user = User::where('id', $data['user_id'])->where('company_id', auth()->user()->company_id)->firstOrFail();

        Advance::create([
            ...$data,
            'user_id' => $user->id,
            'company_id' => auth()->user()->company_id,
            'type' => 'money',
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('manager.payroll.index', ['month' => substr($data['date'], 0, 7), 'user_id' => $data['user_id']])
            ->with('success', 'Vale registrado com sucesso.');
    }

    public function destroy(Advance $advance)
    {
        abort_unless($advance->company_id === auth()->user()->company_id && $advance->type === 'money', 403);
        $month = $advance->date->format('Y-m');
        $advance->delete();
        return redirect()->route('manager.payroll.index', ['month' => $month, 'user_id' => $advance->user_id, 'tab' => 'movements'])->with('success', 'Vale removido.');
    }
}
