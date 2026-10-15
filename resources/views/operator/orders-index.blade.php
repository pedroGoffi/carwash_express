@extends('layouts.app')

@section('title', 'Ordens de Serviço')
@section('header', 'Ordens de Serviço')
@section('subheader', 'Acompanhe todos os veículos, da chegada até a entrega')

@section('content')
@php
    $statusMeta = [
        'waiting' => ['label' => 'Em espera', 'bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'icon' => 'fa-clock'],
        'queued' => ['label' => 'Na fila', 'bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'icon' => 'fa-list-ol'],
        'finished' => ['label' => 'Concluído · entregar chaves', 'bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'icon' => 'fa-key'],
        'delivered' => ['label' => 'Entregue', 'bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'icon' => 'fa-circle-check'],
        'cancelled' => ['label' => 'Cancelada', 'bg' => 'bg-red-100', 'text' => 'text-red-700', 'icon' => 'fa-ban'],
    ];
@endphp

<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800 flex items-center gap-3">
            <i class="fas fa-circle-check"></i><span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-2 xl:grid-cols-5 gap-3">
        @foreach($statusMeta as $key => $meta)
            <a href="{{ route('operator.orders.index', ['status' => $key]) }}"
               class="rounded-2xl border {{ $status === $key ? 'border-blue-500 ring-2 ring-blue-100' : 'border-gray-200' }} bg-white p-4 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between gap-3">
                    <div class="w-10 h-10 {{ $meta['bg'] }} {{ $meta['text'] }} rounded-xl flex items-center justify-center">
                        <i class="fas {{ $meta['icon'] }}"></i>
                    </div>
                    <span class="text-2xl font-bold text-gray-900">{{ $counts[$key] ?? 0 }}</span>
                </div>
                <p class="mt-3 text-sm font-semibold text-gray-700">{{ $meta['label'] }}</p>
            </a>
        @endforeach
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex flex-col lg:flex-row lg:items-center gap-4 justify-between">
            <form method="GET" class="flex flex-1 flex-col sm:flex-row gap-3">
                <div class="relative flex-1 max-w-xl">
                    <i class="fas fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input name="q" value="{{ $search }}" placeholder="Buscar por OS, placa ou cliente"
                           class="w-full rounded-xl border-gray-200 pl-11 pr-4 py-3 focus:border-blue-500 focus:ring-blue-500">
                </div>
                <select name="status" class="rounded-xl border-gray-200 px-4 py-3 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todos os status</option>
                    @foreach($statusMeta as $key => $meta)
                        <option value="{{ $key }}" @selected($status === $key)>{{ $meta['label'] }}</option>
                    @endforeach
                </select>
                <button class="cw-btn-secondary">
                    Filtrar
                </button>
            </form>
            <a href="{{ route('operator.orders.create') }}" class="rounded-xl bg-blue-600 text-white px-5 py-3 font-semibold hover:bg-blue-700 inline-flex items-center justify-center gap-2">
                <i class="fas fa-plus"></i> Nova ordem
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3 font-semibold">Ordem</th>
                        <th class="px-5 py-3 font-semibold">Veículo / cliente</th>
                        <th class="px-5 py-3 font-semibold">Serviços</th>
                        <th class="px-5 py-3 font-semibold">Pagamento</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 font-semibold text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($orders as $order)
                    @php
                        $key = $order->status->value;
                        $meta = $statusMeta[$key] ?? $statusMeta['queued'];
                        $paid = (float) $order->payments->sum('amount');
                        $remaining = max(0, (float) $order->total - $paid);
                    @endphp
                    <tr class="hover:bg-gray-50/70 transition">
                        <td class="px-5 py-4 align-top">
                            <a href="{{ route('operator.orders.receipt', $order) }}" class="font-mono font-bold text-blue-600 hover:underline">#{{ $order->code }}</a>
                            <p class="text-xs text-gray-400 mt-1">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                            <p class="text-sm font-bold text-gray-900 mt-2">R$ {{ number_format($order->total, 2, ',', '.') }}</p>
                        </td>
                        <td class="px-5 py-4 align-top">
                            <div class="font-mono font-bold uppercase text-gray-900">{{ $order->license_plate }}</div>
                            <div class="text-sm text-gray-600 mt-1">{{ $order->customer_name ?: 'Cliente não informado' }}</div>
                            <div class="text-xs text-gray-400 mt-1">{{ strtoupper($order->vehicle_type) }}</div>
                        </td>
                        <td class="px-5 py-4 align-top max-w-xs">
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($order->products->take(3) as $product)
                                    <span class="rounded-lg bg-gray-100 px-2 py-1 text-xs text-gray-700">@if($product->pivot->quantity > 1){{ $product->pivot->quantity }}× @endif{{ $product->name }}</span>
                                @endforeach
                                @if($order->products->count() > 3)
                                    <span class="rounded-lg bg-gray-100 px-2 py-1 text-xs text-gray-500">+{{ $order->products->count() - 3 }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-4 align-top">
                            <p class="text-sm font-semibold {{ $remaining <= 0 ? 'text-emerald-600' : 'text-amber-600' }}">
                                {{ $remaining <= 0 ? 'Pago' : 'Pendente' }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">Pago: R$ {{ number_format($paid, 2, ',', '.') }}</p>
                            @if($remaining > 0)<p class="text-xs text-gray-500">Falta: R$ {{ number_format($remaining, 2, ',', '.') }}</p>@endif
                        </td>
                        <td class="px-5 py-4 align-top">
                            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold {{ $meta['bg'] }} {{ $meta['text'] }}">
                                <i class="fas {{ $meta['icon'] }}"></i>{{ $meta['label'] }}
                            </span>
                        </td>
                        <td class="px-5 py-4 align-top text-right">
                            <div class="flex justify-end flex-wrap gap-2">
                                @if($order->status->value === 'waiting')
                                    <form method="POST" action="{{ route('operator.orders.status', $order) }}">@csrf
                                        <input type="hidden" name="status" value="finished">
                                        <button class="rounded-lg bg-blue-600 text-white px-3 py-2 text-xs font-semibold hover:bg-blue-700"><i class="fas fa-check mr-1"></i> Concluir e liberar</button>
                                    </form>
                                @elseif($order->status->value === 'queued')
                                    <form method="POST" action="{{ route('operator.orders.status', $order) }}">@csrf
                                        <input type="hidden" name="status" value="waiting">
                                        <button class="rounded-lg bg-amber-50 text-amber-700 px-3 py-2 text-xs font-semibold hover:bg-amber-100">Espera</button>
                                    </form>
                                    <form method="POST" action="{{ route('operator.orders.status', $order) }}">@csrf
                                        <input type="hidden" name="status" value="finished">
                                        <button class="rounded-lg bg-emerald-600 text-white px-3 py-2 text-xs font-semibold hover:bg-emerald-700"><i class="fas fa-check mr-1"></i> Concluir</button>
                                    </form>
                                @elseif($order->status->value === 'finished')
                                    <a href="{{ route('operator.orders.deliver-form', $order) }}" class="rounded-lg bg-purple-600 text-white px-3 py-2 text-xs font-semibold hover:bg-purple-700"><i class="fas fa-key mr-1"></i> Entregar</a>
                                @endif
                                <a href="{{ route('operator.orders.receipt', $order) }}" class="rounded-lg bg-gray-100 text-gray-700 px-3 py-2 text-xs font-semibold hover:bg-gray-200"><i class="fas fa-receipt"></i></a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-16 text-center text-gray-500">
                        <div class="w-14 h-14 mx-auto rounded-2xl bg-gray-100 flex items-center justify-center mb-3"><i class="fas fa-clipboard-list text-gray-400 text-xl"></i></div>
                        <p class="font-semibold text-gray-700">Nenhuma ordem encontrada</p>
                        <p class="text-sm mt-1">Ajuste os filtros ou crie uma nova ordem.</p>
                    </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="p-5 border-t border-gray-100">{{ $orders->links() }}</div>
        @endif
    </div>
</div>
@endsection
