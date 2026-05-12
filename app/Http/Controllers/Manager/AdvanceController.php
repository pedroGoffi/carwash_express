<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Advance;
use Illuminate\Http\Request;

class AdvanceController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id;
        
        $advances = Advance::with(['user', 'approvedBy'])
            ->where('company_id', $companyId)
            ->orderBy('date', 'desc')
            ->get();
            
        $workers = User::where('company_id', $companyId)
            ->where('role', 'operator')            
            ->get();
            
        return view('manager.advances.index', compact('advances', 'workers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'reason' => 'nullable|string',
        ]);

        Advance::create([
            'user_id' => $request->user_id,
            'company_id' => auth()->user()->company_id,
            'amount' => $request->amount,
            'date' => $request->date,
            'reason' => $request->reason,
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Atualizar folha do funcionário
        $user = User::find($request->user_id);
        $user->calculatePayroll();

        return redirect()->route('manager.advances.index')
            ->with('success', 'Vale registrado com sucesso!');
    }

    public function destroy(Advance $advance)
    {
        $advance->delete();
        
        // Recalcular folha do funcionário
        $advance->user->calculatePayroll();
        
        return redirect()->route('manager.advances.index')
            ->with('success', 'Vale removido!');
    }
}