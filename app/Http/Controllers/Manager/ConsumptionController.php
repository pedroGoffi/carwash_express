<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Advance;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class ConsumptionController extends Controller
{
    public function index() { return redirect()->route('manager.payroll.index'); }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1|max:100',
            'date' => 'required|date',
            'reason' => 'nullable|string|max:500',
        ]);
        $companyId = auth()->user()->company_id;
        $user = User::where('id', $data['user_id'])->where('company_id', $companyId)->firstOrFail();
        $product = Product::where('id', $data['product_id'])->where('company_id', $companyId)->where('active', true)->with('prices')->firstOrFail();
        $price = (float) $product->getPriceForVehicleType('normal');
        abort_if($price <= 0, 422, 'Produto sem preço definido.');

        Advance::create([
            'user_id' => $user->id,
            'company_id' => $companyId,
            'amount' => $price * $data['quantity'],
            'date' => $data['date'],
            'reason' => $data['reason'] ?? null,
            'type' => 'product',
            'product_id' => $product->id,
            'quantity' => $data['quantity'],
            'unit_price' => $price,
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('manager.payroll.index', ['month' => substr($data['date'], 0, 7), 'user_id' => $data['user_id']])
            ->with('success', 'Consumo registrado com sucesso.');
    }

    public function destroy(Advance $advance)
    {
        abort_unless($advance->company_id === auth()->user()->company_id && $advance->type === 'product', 403);
        $month = $advance->date->format('Y-m');
        $advance->delete();
        return redirect()->route('manager.payroll.index', ['month' => $month, 'user_id' => $advance->user_id, 'tab' => 'movements'])->with('success', 'Consumo removido.');
    }
}
