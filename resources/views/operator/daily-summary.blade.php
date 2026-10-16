@extends('layouts.app')

@section('title', 'Resumo diário')
@section('header', 'Resumo diário')
@section('subheader', 'Lavagens, recebimentos e adicionais por data')

@section('content')
@php
    $money = static fn ($amount) => 'R$ '.number_format((float) $amount, 2, ',', '.');
    $day = \Carbon\CarbonImmutable::createFromFormat('!Y-m-d', $date);
    $yesterday = now()->subDay()->format('Y-m-d');
    $today = now()->format('Y-m-d');
    $vehicles = ['normal' => 'Carros', 'suv' => 'SUVs', 'pickup' => 'Picapes'];
    $methods = ['pix' => 'Pix', 'cash' => 'Dinheiro', 'credit' => 'Cartão de crédito', 'debit' => 'Cartão de débito'];
@endphp
<div class="space-y-6">
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-end gap-4">
        <div>
            <p class="text-xs uppercase tracking-widest text-blue-600 font-extrabold">Operação / Histórico diário</p>
            <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 mt-1">Resumo de {{ $day->format('d/m/Y') }}</h2>
            <p class="text-sm text-slate-500 mt-1">Selecione uma data para visualizar a movimentação daquele dia.</p>
        </div>
        <form method="GET" action="{{ route('operator.daily-summary') }}" class="bg-white rounded-xl border border-slate-200 p-3 flex flex-wrap gap-2 items-end shadow-sm">
            <a href="{{ route('operator.daily-summary', ['date' => $today]) }}" class="px-3 py-2.5 rounded-lg text-sm font-bold {{ $date === $today ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">Hoje</a>
            <a href="{{ route('operator.daily-summary', ['date' => $yesterday]) }}" class="px-3 py-2.5 rounded-lg text-sm font-bold {{ $date === $yesterday ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">D-1 (ontem)</a>
            <label class="text-xs font-bold text-slate-500">Data
                <input type="date" name="date" value="{{ $date }}" class="block mt-1 border border-slate-200 rounded-lg h-10 px-3 text-sm text-slate-900" required>
            </label>
            <button type="submit" class="bg-blue-600 text-white hover:bg-blue-700 rounded-lg px-4 h-10 font-bold text-sm"><i class="fas fa-calendar-check mr-1"></i>Consultar</button>
        </form>
    </div>

    <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <article class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <p class="text-xs font-bold text-slate-500"><i class="fas fa-car-side text-blue-600 mr-2"></i>Ordens / veículos</p>
            <p class="text-3xl font-extrabold text-slate-900 mt-2 tabular-nums">{{ $summary['orders'] }}</p>
            <p class="text-xs text-slate-500 mt-2">Ordens criadas nesta data, sem canceladas</p>
        </article>
        <article class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <p class="text-xs font-bold text-slate-500"><i class="fas fa-wallet text-emerald-600 mr-2"></i>Total recebido</p>
            <p class="text-3xl font-extrabold text-emerald-700 mt-2 tabular-nums">{{ $money($summary['received']) }}</p>
            <p class="text-xs text-slate-500 mt-2">Pagamentos registrados nesta data</p>
        </article>
        <article class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <p class="text-xs font-bold text-slate-500"><i class="fas fa-bottle-water text-amber-600 mr-2"></i>Bebidas vendidas</p>
            <p class="text-3xl font-extrabold text-slate-900 mt-2 tabular-nums">{{ $summary['drinks']['quantity'] }} <span class="text-base text-slate-500">un.</span></p>
            <p class="text-sm font-bold text-amber-700 mt-2">{{ $money($summary['drinks']['amount']) }} em vendas</p>
        </article>
        <article class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <p class="text-xs font-bold text-slate-500"><i class="fas fa-soap text-violet-600 mr-2"></i>Lavagens adicionais</p>
            <p class="text-3xl font-extrabold text-slate-900 mt-2 tabular-nums">{{ $summary['wash_addons']['quantity'] }} <span class="text-base text-slate-500">serviços</span></p>
            <p class="text-sm font-bold text-violet-700 mt-2">{{ $money($summary['wash_addons']['amount']) }} em vendas</p>
        </article>
    </div>

    {{-- Cruzamento de lavagem principal por tipo de carro: não confundir com serviços extras. --}}
    @foreach(['wash_types' => 'Lavagens principais por tipo e veículo', 'wash_addon_types' => 'Lavagens adicionais por tipo e veículo'] as $key => $title)
    <section class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="font-extrabold text-slate-900"><i class="fas fa-soap text-blue-600 mr-2"></i>{{ $title }}</h3>
                <p class="text-xs text-slate-500 mt-1">Quantidade por categoria de veículo · valores vendidos em ordens da data selecionada</p>
            </div>
            <div class="text-right"><strong class="text-sm text-slate-900">{{ $summary[$key]['quantity'] }} serviços</strong>
                <p class="text-xs text-slate-500 tabular-nums">{{ $money($summary[$key]['amount']) }}</p></div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs"><tr>
                    <th class="text-left px-5 py-3">Lavagem</th>
                    <th class="text-center px-3 py-3">Carros</th>
                    <th class="text-center px-3 py-3">SUVs</th>
                    <th class="text-center px-3 py-3">Picapes</th>
                    <th class="text-center px-3 py-3">Total</th>
                    <th class="text-right px-5 py-3">Valor das vendas</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($summary[$key]['items'] as $wash)
                    <tr>
                        <td class="px-5 py-3 font-bold text-slate-800">{{ $wash['name'] }}</td>
                        @foreach(['normal','suv','pickup'] as $vehicle)
                        <td class="px-3 py-3 text-center tabular-nums text-slate-700">
                            <strong class="block">{{ $wash['vehicles'][$vehicle] }}</strong>
                            <span class="block text-[11px] text-slate-400 whitespace-nowrap">{{ $money($wash['vehicle_amounts'][$vehicle]) }}</span>
                        </td>
                        @endforeach
                        <td class="px-3 py-3 text-center font-bold tabular-nums">{{ $wash['quantity'] }}</td>
                        <td class="px-5 py-3 text-right tabular-nums">{{ $money($wash['amount']) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-5 py-6 text-center text-slate-400">Nenhum serviço desta categoria na data selecionada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    @endforeach

    <div class="grid lg:grid-cols-2 gap-5">
        <section class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100">
                <h3 class="font-extrabold text-slate-900"><i class="fas fa-car text-blue-600 mr-2"></i>Lavagens por tipo de veículo</h3>
                <p class="text-xs text-slate-500 mt-1">Quantidade de ordens por categoria no dia selecionado</p>
            </div>
            <div class="p-5 space-y-4">
                @foreach($vehicles as $code => $label)
                    @php $quantity = (int) ($summary['vehicles'][$code] ?? 0); @endphp
                    <div>
                        <div class="flex justify-between text-sm mb-1"><span class="font-semibold text-slate-700">{{ $label }}</span><strong class="tabular-nums">{{ $quantity }}</strong></div>
                        <div class="bg-slate-100 h-2 rounded-full overflow-hidden"><div class="bg-blue-600 h-2 rounded-full" style="width: {{ $summary['orders'] > 0 ? round($quantity / $summary['orders'] * 100) : 0 }}%"></div></div>
                    </div>
                @endforeach
            </div>
        </section>
        <section class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100">
                <h3 class="font-extrabold text-slate-900"><i class="fas fa-credit-card text-emerald-600 mr-2"></i>Pagamentos por modalidade</h3>
                <p class="text-xs text-slate-500 mt-1">Valores realmente recebidos nesta data, mesmo de ordens anteriores</p>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($methods as $code => $label)
                    <div class="px-5 py-3 flex justify-between items-center gap-3">
                        <div><p class="text-sm font-bold text-slate-800">{{ $label }}</p><p class="text-xs text-slate-500">{{ $summary['payments'][$code]['quantity'] ?? 0 }} lançamento(s)</p></div>
                        <strong class="text-sm text-slate-900 tabular-nums">{{ $money($summary['payments'][$code]['amount'] ?? 0) }}</strong>
                    </div>
                @endforeach
                <div class="px-5 py-4 flex justify-between bg-emerald-50 text-emerald-800 font-extrabold"><span>Total recebido</span><strong>{{ $money($summary['received']) }}</strong></div>
            </div>
        </section>
    </div>

    <div class="grid lg:grid-cols-2 gap-5">
        @foreach(['drinks' => ['title' => 'Bebidas vendidas', 'icon' => 'fa-bottle-water'], 'wash_addons' => ['title' => 'Serviços extras de lavagem', 'icon' => 'fa-soap']] as $key => $heading)
            <section class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center gap-4">
                    <h3 class="font-extrabold text-slate-900"><i class="fas {{ $heading['icon'] }} text-blue-600 mr-2"></i>{{ $heading['title'] }}</h3>
                    <strong class="text-sm tabular-nums">{{ $money($summary[$key]['amount']) }}</strong>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($summary[$key]['items'] as $item)
                        <div class="px-5 py-3 flex items-center justify-between gap-4">
                            <p class="text-sm font-medium text-slate-700">{{ $item['name'] }}</p>
                            <div class="text-right flex-shrink-0"><strong class="text-sm text-slate-900">{{ $item['quantity'] }} un.</strong><p class="text-xs text-slate-500">{{ $money($item['amount']) }}</p></div>
                        </div>
                    @empty
                        <p class="px-5 py-8 text-center text-sm text-slate-400">Nenhuma venda registrada nesta data.</p>
                    @endforelse
                </div>
                @if(count($summary[$key]['items']) >= 5)
                    <p class="px-5 py-3 text-xs text-slate-400 border-t border-slate-100">Exibindo até cinco produtos com maior quantidade.</p>
                @endif
            </section>
        @endforeach
    </div>
    <p class="text-xs text-slate-500">Importante: bebidas e serviços adicionais representam o valor de itens em ordens criadas no dia selecionado. Recebimentos usam a data real do pagamento. Não some esses valores ao total recebido, pois representam medidas diferentes.</p>
</div>
@endsection
