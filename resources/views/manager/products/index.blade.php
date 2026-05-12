@extends('layouts.app')

@section('title', 'Produtos')
@section('header', 'Gerenciar Produtos')
@section('subheader', 'Cadastre e gerencie os serviços e produtos')

@section('content')
<div class="space-y-6">
    
    {{-- Botão Novo Produto --}}
    <div class="flex justify-end">
        <a href="{{ route('manager.products.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-2">
            <i class="fas fa-plus"></i> Novo Produto
        </a>
    </div>

    {{-- Tabs por tipo --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="border-b">
            <div class="flex">
                <button onclick="filterType('all')" class="tab-btn px-6 py-3 text-sm font-medium border-b-2 border-blue-500 text-blue-600">
                    Todos
                </button>
                <button onclick="filterType('wash')" class="tab-btn px-6 py-3 text-sm font-medium text-gray-500 hover:text-gray-700">
                    <i class="fas fa-soap mr-1"></i> Lavagens
                </button>
                <button onclick="filterType('drink')" class="tab-btn px-6 py-3 text-sm font-medium text-gray-500 hover:text-gray-700">
                    <i class="fas fa-bottle-water mr-1"></i> Bebidas
                </button>
                <button onclick="filterType('snack')" class="tab-btn px-6 py-3 text-sm font-medium text-gray-500 hover:text-gray-700">
                    <i class="fas fa-hamburger mr-1"></i> Comidas
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Preço (Carro)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($products as $product)
                    <tr class="product-row" data-type="{{ $product->type->value }}">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg 
                                    {{ $product->type->value == 'wash' ? 'bg-blue-100' : ($product->type->value == 'drink' ? 'bg-green-100' : 'bg-orange-100') }}">
                                    <i class="fas {{ $product->type->value == 'wash' ? 'fa-car-wash' : ($product->type->value == 'drink' ? 'fa-bottle-water' : 'fa-hamburger') }} 
                                        {{ $product->type->value == 'wash' ? 'text-blue-500' : ($product->type->value == 'drink' ? 'text-green-500' : 'text-orange-500') }}
                                        w-8 h-8 flex items-center justify-center"></i>
                                </div>
                                <div>
                                    <p class="font-medium">{{ $product->name }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full 
                                {{ $product->type->value == 'wash' ? 'bg-blue-100 text-blue-800' : ($product->type->value == 'drink' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800') }}">
                                {{ $product->type->label() }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-bold text-green-600">
                                R$ {{ number_format($product->getPriceForVehicleType('normal'), 2, ',', '.') }}
                            </span>
                            @if($product->type->value == 'wash')
                            <div class="text-xs text-gray-400 mt-1">
                                SUV: R$ {{ number_format($product->getPriceForVehicleType('suv'), 2, ',', '.') }} |
                                Pickup: R$ {{ number_format($product->getPriceForVehicleType('pickup'), 2, ',', '.') }}
                            </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full {{ $product->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $product->active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('manager.products.edit', $product) }}" class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('manager.products.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('Excluir produto?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">Nenhum produto cadastrado</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function filterType(type) {
        const rows = document.querySelectorAll('.product-row');
        const tabs = document.querySelectorAll('.tab-btn');
        
        tabs.forEach(tab => {
            tab.classList.remove('border-blue-500', 'text-blue-600');
            tab.classList.add('text-gray-500');
        });
        
        if (type === 'all') {
            tabs[0].classList.add('border-blue-500', 'text-blue-600');
            rows.forEach(row => row.style.display = '');
        } else {
            const clickedTab = Array.from(tabs).find(tab => tab.textContent.toLowerCase().includes(type));
            if (clickedTab) {
                clickedTab.classList.add('border-blue-500', 'text-blue-600');
            }
            rows.forEach(row => {
                row.style.display = row.dataset.type === type ? '' : 'none';
            });
        }
    }
</script>
@endsection