@extends('layouts.app')

@section('title', 'Editar Produto')
@section('header', 'Editar Produto')
@section('subheader', 'Altere as informações do produto')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6">
        
        <form action="{{ route('manager.products.update', $product) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Produto *</label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                    <select name="type" id="product-type" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="wash" {{ $product->type->value == 'wash' ? 'selected' : '' }}>Lavagem</option>
                        <option value="drink" {{ $product->type->value == 'drink' ? 'selected' : '' }}>Bebida</option>
                        <option value="snack" {{ $product->type->value == 'snack' ? 'selected' : '' }}>Comida</option>
                        <option value="other" {{ old('type', isset($product) ? $product->type->value : '') === 'other' ? 'selected' : '' }}>Outros produtos</option>
                    </select>
                </div>

                {{-- Preços de venda por Tipo de Veículo --}}
                <div class="bg-gray-50 rounded-lg p-4">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        <i class="fas fa-table mr-1"></i> Tabela de Preços
                    </label>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2 px-3 text-sm font-medium">Categoria</th>
                                    <th class="text-left py-2 px-3 text-sm font-medium">Preço (R$)</th>
                                    <th class="text-left py-2 px-3 text-sm font-medium w-20"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b">
                                    <td class="py-2 px-3">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-car-side text-blue-500"></i>
                                            <span class="text-sm">Preço base / PDV</span>
                                        </div>
                                    </td>
                                    <td class="py-2 px-3">
                                        <input type="number" name="prices[normal]" step="0.01" required
                                               value="{{ old('prices.normal', $prices['normal']->price ?? '') }}"
                                               class="w-32 border rounded-lg px-2 py-1 text-sm">
                                    </td>
                                    <td class="py-2 px-3 text-xs text-gray-400">Base</td>
                                </tr>
                                <tr class="border-b" data-vehicle-price>
                                    <td class="py-2 px-3">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-caravan text-green-500"></i>
                                            <span class="text-sm">SUV / Picape</span>
                                        </div>
                                    </td>
                                    <td class="py-2 px-3">
                                        <input type="number" name="prices[suv]" step="0.01"
                                               value="{{ old('prices.suv', $prices['suv']->price ?? '') }}"
                                               class="w-32 border rounded-lg px-2 py-1 text-sm">
                                    </td>
                                    <td class="py-2 px-3 text-xs text-gray-400">Opcional</td>
                                </tr>
                                <tr class="border-b">
                                    <td class="py-2 px-3">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-truck-pickup text-orange-500"></i>
                                            <span class="text-sm">Caminhonete Grande</span>
                                        </div>
                                    </td>
                                    <td class="py-2 px-3">
                                        <input type="number" name="prices[pickup]" step="0.01"
                                               value="{{ old('prices.pickup', $prices['pickup']->price ?? '') }}"
                                               class="w-32 border rounded-lg px-2 py-1 text-sm">
                                    </td>
                                    <td class="py-2 px-3 text-xs text-gray-400">Opcional</td>
                                </tr>                                
                            </tbody>
                        </table>
                    </div>
                    <p class="text-xs text-gray-400 mt-3">
                        <i class="fas fa-info-circle"></i> Para bebidas e mercadorias, informe apenas o preço base / PDV. Para lavagens, é possível definir preços por veículo.
                    </p>
                </div>

                <div>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="active" value="1" {{ $product->active ? 'checked' : '' }}>
                        <span class="text-sm text-gray-700">Produto ativo</span>
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <a href="{{ route('manager.products.index') }}" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancelar</a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Atualizar</button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
(() => {
    const type = document.getElementById('product-type');
    if (!type) return;
    const refresh = () => document.querySelectorAll('[data-vehicle-price]').forEach(row => row.hidden = type.value !== 'wash');
    type.addEventListener('change', refresh);
    refresh();
})();
</script>
@endsection