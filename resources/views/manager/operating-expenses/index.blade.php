@extends('layouts.app')

@section('title', 'Custos operacionais')
@section('header', 'Custos operacionais')
@section('subheader', 'Energia, água, aluguel, manutenção e demais despesas da empresa')

@section('content')
@php
    $categories = \App\Models\OperatingExpense::CATEGORIES;
    $money = static fn ($value) => 'R$ '.number_format((float) $value, 2, ',', '.');
@endphp
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold tracking-tight text-slate-900">Despesas da operação</h2>
            <p class="text-sm text-slate-500 mt-1">Lançamentos independentes da folha, adiantamentos e despesas individuais de funcionários.</p>
        </div>
        <form method="GET" action="{{ route('manager.operating-expenses.index') }}" class="flex flex-wrap gap-2 items-end bg-white border border-slate-200 rounded-xl p-3">
            <label class="text-xs font-bold text-slate-600">Competência
                <input type="month" name="month" value="{{ $month }}" class="block mt-1 h-10 rounded-lg border border-slate-200 px-3 text-sm" required>
            </label>
            <button type="submit" class="bg-blue-600 text-white rounded-lg px-4 h-10 font-bold text-sm hover:bg-blue-700">Consultar</button>
        </form>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl p-3 text-sm">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 text-sm">
            <strong>Revise o lançamento:</strong>
            <ul class="mt-2 list-disc ml-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="grid md:grid-cols-3 gap-4">
        <div class="md:col-span-1 bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <p class="font-bold text-xs text-slate-500"><i class="fas fa-wallet text-blue-600 mr-2"></i>Total de custos no mês</p>
            <p class="text-3xl font-extrabold text-slate-900 mt-2 tabular-nums">{{ $money($total) }}</p>
            <p class="text-xs text-slate-500 mt-2">Competência {{ \Carbon\CarbonImmutable::createFromFormat('!Y-m', $month)->format('m/Y') }}</p>
        </div>
        <div class="md:col-span-2 bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <h3 class="text-sm font-extrabold text-slate-900 mb-3">Distribuição por categoria</h3>
            <div class="grid sm:grid-cols-2 gap-x-6 gap-y-2">
                @forelse($byCategory as $category)
                    <div class="flex justify-between text-sm border-b border-slate-100 pb-2 gap-2">
                        <span class="text-slate-600">{{ $categories[$category->category] ?? $category->category }} <span class="text-xs text-slate-400">({{ $category->quantity }})</span></span>
                        <strong class="whitespace-nowrap tabular-nums">{{ $money($category->total) }}</strong>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">Nenhum custo registrado nesta competência.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid xl:grid-cols-[360px_minmax(0,1fr)] gap-5 items-start">
        <section class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <h3 class="font-extrabold text-slate-900 mb-1">Adicionar despesa</h3>
            <p class="text-xs text-slate-500 mb-5">Lançamentos de luz, água, aluguel e outros custos.</p>
            <form method="POST" action="{{ route('manager.operating-expenses.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="expense-category" class="block text-xs font-bold text-slate-600 mb-1">Categoria *</label>
                    <select id="expense-category" name="category" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm" required>
                        <option value="">Selecione...</option>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" @selected(old('category') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="expense-date" class="block text-xs font-bold text-slate-600 mb-1">Data do custo *</label>
                    <input id="expense-date" type="date" name="date" value="{{ old('date', now()->format('Y-m-d')) }}" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm" required>
                </div>
                <div>
                    <label for="expense-amount" class="block text-xs font-bold text-slate-600 mb-1">Valor em R$ *</label>
                    <input id="expense-amount" type="number" name="amount" min="0.01" step="0.01" value="{{ old('amount') }}" placeholder="0,00" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm" required>
                </div>
                <div>
                    <label for="expense-description" class="block text-xs font-bold text-slate-600 mb-1">Descrição / referência</label>
                    <textarea id="expense-description" name="description" rows="3" maxlength="500" placeholder="Ex.: Conta de energia da unidade referente a outubro" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">{{ old('description') }}</textarea>
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl"><i class="fas fa-plus mr-2"></i>Registrar custo</button>
            </form>
        </section>
        <section class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100"><h3 class="font-extrabold text-slate-900">Lançamentos do mês</h3><p class="text-xs text-slate-500 mt-1">Histórico de custos operacionais, por competência</p></div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="p-4">Data</th><th class="p-4">Categoria / descrição</th><th class="p-4 text-right">Valor</th><th class="p-4 text-right">Ação</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($expenses as $expense)
                            <tr>
                                <td class="p-4 text-slate-600 whitespace-nowrap">{{ $expense->date->format('d/m/Y') }}</td>
                                <td class="p-4"><p class="font-bold text-slate-800">{{ $categories[$expense->category] ?? $expense->category }}</p><p class="text-xs text-slate-500 mt-1">{{ $expense->description ?: 'Sem descrição' }}</p></td>
                                <td class="p-4 text-right font-extrabold tabular-nums whitespace-nowrap">{{ $money($expense->amount) }}</td>
                                <td class="p-4 text-right">
                                    <form action="{{ route('manager.operating-expenses.destroy', $expense) }}" method="POST" onsubmit="return confirm('Excluir este custo operacional?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" aria-label="Excluir custo" class="p-2 rounded-lg text-red-600 hover:bg-red-50"><i class="fas fa-trash-can"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-12 text-slate-400">Nenhum custo operacional lançado neste mês.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($expenses->hasPages())<div class="p-4 border-t border-slate-100">{{ $expenses->links() }}</div>@endif
        </section>
    </div>
    <p class="text-xs text-slate-500">Custos operacionais compõem os custos mensais do dashboard gerencial, sem serem abatidos da folha individual.</p>
</div>
@endsection
