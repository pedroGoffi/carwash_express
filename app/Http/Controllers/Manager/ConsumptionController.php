<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Advance;
use Illuminate\Http\Request;

class ConsumptionController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id;
        
        $consumptions = Advance::with(['user', 'product', 'approvedBy'])
            ->where('company_id', $companyId)
            ->where('type', 'product')
            ->orderBy('date', 'desc')
            ->get();
            
        $workers = User::where('company_id', $companyId)
            ->where('role', 'operator')            
            ->get();
            
        $products = Product::where('company_id', $companyId)
            ->where('active', true)
            ->whereIn('type', ['drink', 'snack'])
            ->get();
                    
        return view('manager.consumptions.index', compact('consumptions', 'workers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'date' => 'required|date',
            'reason' => 'nullable|string',
        ]);

        $product = Product::find($request->product_id);
        
        // Verificar se o produto existe
        if (!$product) {
            return redirect()->back()->with('error', 'Produto não encontrado!');
        }
        
        // Verificar se o produto tem preço
        $price = $product->getPriceForVehicleType('normal');
        if ($price <= 0) {
            return redirect()->back()->with('error', 'Produto sem preço definido!');
        }
        
        $totalPrice = $price * $request->quantity;

        Advance::create([
            'user_id' => $request->user_id,
            'company_id' => auth()->user()->company_id,
            'amount' => $totalPrice,
            'date' => $request->date,
            'reason' => $request->reason,
            'type' => 'product',
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'unit_price' => $price,
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Recalcular folha
        $user = User::find($request->user_id);
        if ($user) {
            $user->calculatePayroll();
        }

        return redirect()->route('manager.consumptions.index')
            ->with('success', 'Consumo registrado com sucesso!');
    }

    public function destroy(Advance $advance)
    {
        if ($advance->type !== 'product') {
            return redirect()->back()->with('error', 'Item inválido');
        }
        
        $advance->delete();
        $advance->user->calculatePayroll();
        
        return redirect()->route('manager.consumptions.index')
            ->with('success', 'Consumo removido!');
    }
}