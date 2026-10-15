@extends('layouts.app')
@section('title', 'Administração')
@section('header', 'Administração')
@section('subheader', 'Visão geral do CarWash Express')
@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach([
            ['label' => 'Empresas', 'value' => number_format($totalCompanies, 0, ',', '.')],
            ['label' => 'Usuários', 'value' => number_format($totalUsers, 0, ',', '.')],
            ['label' => 'Ordens', 'value' => number_format($totalOrders, 0, ',', '.')],
            ['label' => 'Receita registrada', 'value' => 'R$ '.number_format($totalRevenue, 2, ',', '.')],
        ] as $metric)
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-medium text-slate-500">{{ $metric['label'] }}</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $metric['value'] }}</p>
            </section>
        @endforeach
    </div>
    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-100 px-5 py-4 flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-slate-900">Ordens recentes</h2>
            <a class="text-blue-600 text-sm font-semibold" href="{{ route('operator.orders.index') }}">Ver todas</a>
        </div>
        <div class="overflow-x-auto"><table class="w-full min-w-[620px] text-sm">
            <thead class="bg-slate-50 text-slate-500"><tr><th class="text-left p-4">OS</th><th class="text-left p-4">Empresa</th><th class="text-left p-4">Cliente</th><th class="text-right p-4">Total</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
            @forelse($recentOrders as $order)
                <tr><td class="p-4">{{ $order->code ?? '#'.$order->id }}</td><td class="p-4">{{ $order->company?->legal_name ?? '—' }}</td><td class="p-4">{{ $order->customer_name ?? '—' }}</td><td class="p-4 text-right">R$ {{ number_format((float) $order->total, 2, ',', '.') }}</td></tr>
            @empty
                <tr><td class="p-8 text-center text-slate-500" colspan="4">Nenhuma ordem registrada.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </section>
</div>
@endsection
