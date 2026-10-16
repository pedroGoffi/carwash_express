<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Enums\ProductType;
use App\Enums\VehicleType;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::where('company_id', auth()->user()->company_id)
            ->with('prices')
            ->orderBy('type')
            ->orderBy('name')
            ->get();
            
        return view('manager.products.index', compact('products'));
    }

    public function create()
    {
        $vehicleTypes = VehicleType::cases();
        return view('manager.products.create', compact('vehicleTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:wash,drink,snack,other',
            'prices' => 'required|array:normal,suv,pickup',
            'prices.suv' => 'nullable|numeric|min:0',
            'prices.pickup' => 'nullable|numeric|min:0',
            'prices.normal' => 'required|numeric|min:0',
        ]);

        $product = Product::create([
            'company_id' => auth()->user()->company_id,
            'name' => $request->name,
            'type' => $request->type,
            'active' => true,
        ]);

        foreach ($request->prices as $vehicleType => $price) {
            if ($price !== null && $price !== '') {
                ProductPrice::create([
                    'product_id' => $product->id,
                    'vehicle_type' => $vehicleType,
                    'price' => $price,
                ]);
            }
        }

        return redirect()->route('manager.products.index')
            ->with('success', 'Produto criado com sucesso!');
    }

    public function edit(Product $product)
    {
        $this->authorizeCompany($product);
        
        $vehicleTypes = VehicleType::cases();
        $prices = [];
        foreach ($product->prices as $price) {
            $prices[$price->vehicle_type->value] = $price;
        }
        
        return view('manager.products.edit', compact('product', 'vehicleTypes', 'prices'));
    }

    public function update(Request $request, Product $product)
    {
        $this->authorizeCompany($product);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:wash,drink,snack,other',
            'active' => 'boolean',
            'prices' => 'required|array:normal,suv,pickup',
            'prices.suv' => 'nullable|numeric|min:0',
            'prices.pickup' => 'nullable|numeric|min:0',
            'prices.normal' => 'required|numeric|min:0',
        ]);

        if ($product->serviceOrders()->exists() && $request->type !== $product->type->value) {
            throw ValidationException::withMessages(['type' => 'Produtos já vendidos não podem mudar de categoria. Desative o antigo e cadastre outro produto.']);
        }

        $product->update([
            'name' => $request->name,
            'type' => $request->type,
            'active' => $request->has('active'),
        ]);

        foreach ($request->prices as $vehicleType => $price) {
            if ($price !== null && $price !== '') {
                ProductPrice::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'vehicle_type' => $vehicleType,
                    ],
                    ['price' => $price]
                );
            }
        }

        return redirect()->route('manager.products.index')
            ->with('success', 'Produto atualizado!');
    }

    public function destroy(Product $product)
    {
        $this->authorizeCompany($product);
        if ($product->serviceOrders()->exists() || \App\Models\Advance::where('product_id', $product->id)->exists()) {
            $product->update(['active' => false]);
            return redirect()->route('manager.products.index')
                ->with('success', 'Produto desativado; o histórico das vendas foi preservado.');
        }
        $product->prices()->delete();
        $product->delete();
        return redirect()->route('manager.products.index')
            ->with('success', 'Produto removido!');
    }

    private function authorizeCompany($product)
    {
        if ($product->company_id !== auth()->user()->company_id) {
            abort(403);
        }
    }
}