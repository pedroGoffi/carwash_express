@extends('layouts.app')
@section('title','Folha e custos')
@section('header','Folha e custos')
@section('subheader','Salários, descontos e despesas da equipe em um único lugar')
@section('content')
@php
    $month = $period->format('Y-m');
    $employee = $userId ? $team->firstWhere('id', $userId) : null;
    $cats = ['benefit'=>'Benefícios','transport'=>'Transporte','meal'=>'Alimentação','tax'=>'Encargos','bonus'=>'Bonificação','equipment'=>'Equipamentos','other'=>'Outros'];
    $tabLink = fn ($t) => route('manager.payroll.index', array_filter(['month'=>$month,'user_id'=>$userId,'tab'=>$t]));
@endphp
<div class="space-y-5">
    <div class="cw-card p-5">
        <div class="flex flex-col lg:flex-row justify-between lg:items-center gap-4">
            <div>
                <p class="cw-eyebrow">Competência financeira</p>
                <h2 class="text-xl font-bold text-slate-900 mt-1">{{ $period->locale('pt_BR')->translatedFormat('F \d\e Y') }}</h2>
                <p class="text-sm text-slate-500 mt-1">{{ $employee ? 'Custos exclusivos de '.$employee->name : 'Visão geral de colaboradores ativos' }} · {{ $closedForPeriod ? 'Fechamento registrado' : 'Competência aberta' }}</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2">
                <form method="GET" action="{{ route('manager.payroll.index') }}" class="flex flex-wrap items-center gap-2">
                    <input type="month" name="month" value="{{ $month }}" aria-label="Competência" class="cw-input max-w-44" required>
                    <select name="user_id" aria-label="Filtrar colaborador" class="cw-input max-w-64">
                        <option value="">Todos os colaboradores</option>
                        @foreach($team as $person)<option value="{{ $person->id }}" @selected($userId == $person->id)>{{ $person->name }}{{ !$person->active ? ' (inativo)' : '' }}</option>@endforeach
                    </select>
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <button class="cw-btn-secondary" type="submit"><i class="fas fa-filter mr-2"></i>Filtrar</button>
                </form>
                <a href="{{ route('manager.users.create') }}" class="cw-btn-primary"><i class="fas fa-user-plus mr-2"></i>Colaborador</a>
            </div>
        </div>
    </div>
    @if(session('success'))<div class="cw-message" role="status"><i class="fas fa-circle-check"></i> {{ session('success') }}</div>@endif
    @if($errors->any())<div class="cw-error" role="alert"><strong>Verifique os campos:</strong> {{ $errors->first() }}</div>@endif

    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3">
        @foreach([
            ['Folha base','base','fa-wallet'],['Vales','advances','fa-hand-holding-dollar'],
            ['Consumos','consumptions','fa-mug-hot'],['Outros custos','extras','fa-receipt'],
            ['Líquido da folha','net','fa-money-bill-wave'],['Custo estimado','employer','fa-chart-line']
        ] as [$label,$key,$icon])
            <div class="cw-card p-4">
                <div class="flex items-center gap-2 text-slate-500 text-xs"><i class="fas {{ $icon }} text-blue-500"></i>{{ $label }}</div>
                <p class="text-xl font-bold text-slate-900 mt-3 tabular-nums">R$ {{ number_format($summary[$key],2,',','.') }}</p>
            </div>
        @endforeach
    </div>
    <p class="text-xs text-slate-500">Custo estimado = folha base + despesas extras lançadas. Vales e consumos já reduzem o líquido, não são somados novamente ao custo. Encargos legais só entram se forem lançados em despesas extras.</p>
    <nav class="cw-card flex gap-2 p-2 overflow-x-auto" aria-label="Seções financeiras">
        <a href="{{ $tabLink('overview') }}" class="cw-tab {{ $tab==='overview' ? 'cw-tab-active' : '' }}"><i class="fas fa-users mr-2"></i>Equipe e lançamentos</a>
        <a href="{{ $tabLink('movements') }}" class="cw-tab {{ $tab==='movements' ? 'cw-tab-active' : '' }}"><i class="fas fa-list mr-2"></i>Extrato de custos</a>
        <a href="{{ $tabLink('history') }}" class="cw-tab {{ $tab==='history' ? 'cw-tab-active' : '' }}"><i class="fas fa-clock-rotate-left mr-2"></i>Histórico fechado</a>
    </nav>

    @if($tab==='overview')
        <div class="grid lg:grid-cols-3 gap-4">
            <form action="{{ route('manager.advances.store') }}" method="POST" class="cw-card p-5 space-y-3">@csrf
                <h3 class="font-bold text-slate-900"><i class="fas fa-money-bill-transfer text-blue-600 mr-2"></i>Vale em dinheiro</h3>
                <p class="text-xs text-slate-500">Desconto da folha do colaborador.</p>
                <select name="user_id" required class="cw-input"><option value="">Escolha o colaborador</option>@foreach($team->where('active',true) as $person)<option value="{{ $person->id }}" @selected(old('user_id',$userId)==$person->id)>{{ $person->name }}</option>@endforeach</select>
                <div class="grid grid-cols-2 gap-2"><input class="cw-input" name="amount" aria-label="Valor do vale" type="number" step="0.01" min="0.01" placeholder="R$ 0,00" required><input class="cw-input" name="date" aria-label="Data do vale" type="date" value="{{ $period->isCurrentMonth()?now()->toDateString():$period->toDateString() }}" required></div>
                <input class="cw-input" name="reason" maxlength="500" placeholder="Motivo (opcional)">
                <button class="cw-btn-primary w-full" type="submit">Registrar vale</button>
            </form>
            <form action="{{ route('manager.consumptions.store') }}" method="POST" class="cw-card p-5 space-y-3">@csrf
                <h3 class="font-bold text-slate-900"><i class="fas fa-mug-hot text-blue-600 mr-2"></i>Consumo de produto</h3>
                <p class="text-xs text-slate-500">Produto consumido descontado da folha.</p>
                <select name="user_id" required class="cw-input"><option value="">Escolha o colaborador</option>@foreach($team->where('active',true) as $person)<option value="{{ $person->id }}" @selected(old('user_id',$userId)==$person->id)>{{ $person->name }}</option>@endforeach</select>
                <select class="cw-input" name="product_id" required><option value="">Selecione o produto</option>@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->name }} · R$ {{ number_format($product->getPriceForVehicleType('normal'),2,',','.') }}</option>@endforeach</select>
                <div class="grid grid-cols-2 gap-2"><input class="cw-input" type="number" min="1" max="100" name="quantity" aria-label="Quantidade" value="1" required><input class="cw-input" name="date" type="date" aria-label="Data do consumo" value="{{ $period->isCurrentMonth()?now()->toDateString():$period->toDateString() }}" required></div>
                <button class="cw-btn-primary w-full" type="submit">Registrar consumo</button>
            </form>
            <form action="{{ route('manager.expenses.store') }}" method="POST" class="cw-card p-5 space-y-3">@csrf
                <h3 class="font-bold text-slate-900"><i class="fas fa-file-invoice-dollar text-blue-600 mr-2"></i>Despesa do funcionário</h3>
                <p class="text-xs text-slate-500">Custo extra; não reduz o salário líquido.</p>
                <select name="user_id" required class="cw-input"><option value="">Escolha o colaborador</option>@foreach($team->where('active',true) as $person)<option value="{{ $person->id }}" @selected(old('user_id',$userId)==$person->id)>{{ $person->name }}</option>@endforeach</select>
                <select name="category" required class="cw-input"><option value="">Categoria</option>@foreach($cats as $code=>$text)<option value="{{ $code }}">{{ $text }}</option>@endforeach</select>
                <div class="grid grid-cols-2 gap-2"><input class="cw-input" name="amount" type="number" step="0.01" min="0.01" aria-label="Valor da despesa" placeholder="R$ 0,00" required><input class="cw-input" name="date" type="date" aria-label="Data da despesa" value="{{ $period->isCurrentMonth()?now()->toDateString():$period->toDateString() }}" required></div>
                <input class="cw-input" name="description" maxlength="500" placeholder="Descrição (opcional)">
                <button class="cw-btn-primary w-full" type="submit">Adicionar despesa</button>
            </form>
        </div>
        <section class="cw-card overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3"><div><h3 class="font-bold text-slate-900">Custos por colaborador</h3><p class="text-xs text-slate-500 mt-1">Clique em “Ver gastos” para isolar o extrato de uma pessoa.</p></div><a href="{{ route('manager.users.index') }}" class="cw-btn-secondary">Gerenciar equipe</a></div>
            <div class="overflow-x-auto"><table class="cw-table"><thead><tr><th>Colaborador</th><th class="text-right">Salário base</th><th class="text-right">Vales</th><th class="text-right">Consumos</th><th class="text-right">Outros custos</th><th class="text-right">Líquido</th><th class="text-right">Ações</th></tr></thead>
            <tbody>@forelse($workers as $worker)
                <tr><td><div class="font-semibold text-slate-900">{{ $worker->name }}</div><div class="text-xs text-slate-500">{{ (float)$worker->daily_rate>0 ? $worker->days_worked.' dias × R$ '.number_format($worker->daily_rate,2,',','.') : 'Mensalista' }}</div></td>
                <td class="text-right whitespace-nowrap">R$ {{ number_format($worker->base_amount,2,',','.') }}</td>
                <td class="text-right whitespace-nowrap">R$ {{ number_format($worker->money_advances,2,',','.') }}</td>
                <td class="text-right whitespace-nowrap">R$ {{ number_format($worker->product_consumptions,2,',','.') }}</td>
                <td class="text-right whitespace-nowrap">R$ {{ number_format($worker->extra_costs,2,',','.') }}</td>
                <td class="text-right whitespace-nowrap font-bold text-slate-900">R$ {{ number_format($worker->net_amount,2,',','.') }}</td>
                <td class="text-right whitespace-nowrap"><a class="text-sm font-semibold text-blue-600 hover:underline mr-3" href="{{ route('manager.payroll.index',['month'=>$month,'user_id'=>$worker->id,'tab'=>'movements']) }}">Ver gastos</a><a class="text-sm font-semibold text-slate-600 hover:underline" href="{{ route('manager.users.edit',$worker) }}">Editar cadastro</a></td></tr>
            @empty<tr><td colspan="7" class="text-center text-slate-500 py-10">Nenhum colaborador para o filtro escolhido.</td></tr>@endforelse</tbody></table></div>
        </section>
        <div class="cw-card p-5 flex flex-col sm:flex-row gap-4 sm:items-center justify-between">
            <div><h3 class="font-semibold text-slate-900">Fechamento mensal</h3><p class="text-xs text-slate-500 mt-1">Registra os valores atuais da folha. Revise a competência antes de confirmar.</p></div>
            @if(!auth()->user()->isOperator())
            <form method="POST" action="{{ route('manager.payroll.close-period') }}" onsubmit="return confirm('Fechar a competência? Um novo fechamento atualiza o histórico desse mês.')">@csrf<input type="hidden" name="month" value="{{ $month }}"><button class="cw-btn-primary"><i class="fas fa-lock mr-2"></i>{{ $closedForPeriod ? 'Atualizar fechamento' : 'Fechar competência' }}</button></form>
            @else
                <span class="text-xs text-slate-500">Fechamento disponível somente para gerente/administrador</span>
            @endif
        </div>
    @elseif($tab==='movements')
        <section class="cw-card overflow-hidden"><div class="p-5 border-b border-slate-100"><h3 class="font-bold text-slate-900">Vales e consumos {{ $employee ? 'de '.$employee->name : '' }}</h3><p class="text-xs text-slate-500 mt-1">Lançamentos que reduzem o valor líquido da folha.</p></div>
        <div class="overflow-x-auto"><table class="cw-table"><thead><tr><th>Data</th><th>Funcionário</th><th>Categoria</th><th>Detalhe</th><th class="text-right">Valor</th><th class="text-right">Ação</th></tr></thead><tbody>
        @forelse($transactions as $t)<tr><td>{{ $t->date->format('d/m/Y') }}</td><td>{{ $t->user?->name }}</td><td>{{ $t->type === 'product' ? 'Consumo' : 'Vale' }}</td><td>{{ $t->type === 'product' ? ($t->product?->name ?? 'Produto').' × '.$t->quantity : ($t->reason ?: 'Adiantamento') }}</td><td class="text-right font-semibold">R$ {{ number_format($t->amount,2,',','.') }}</td><td class="text-right"><form method="POST" action="{{ $t->type === 'product' ? route('manager.consumptions.destroy',$t) : route('manager.advances.destroy',$t) }}" onsubmit="return confirm('Excluir lançamento?')">@csrf @method('DELETE')<button class="cw-action-danger" aria-label="Remover lançamento"><i class="fas fa-trash"></i></button></form></td></tr>
        @empty<tr><td colspan="6" class="text-center py-8 text-slate-500">Nenhum vale ou consumo nesta competência.</td></tr>@endforelse
        </tbody></table></div><div class="p-4">{{ $transactions->links() }}</div></section>
        <section class="cw-card overflow-hidden"><div class="p-5 border-b border-slate-100"><h3 class="font-bold text-slate-900">Despesas extras {{ $employee ? 'de '.$employee->name : '' }}</h3><p class="text-xs text-slate-500 mt-1">Benefícios, transporte, alimentação, encargos e demais custos por colaborador.</p></div>
        <div class="overflow-x-auto"><table class="cw-table"><thead><tr><th>Data</th><th>Funcionário</th><th>Categoria</th><th>Descrição</th><th class="text-right">Valor</th><th class="text-right">Ação</th></tr></thead><tbody>
        @forelse($expenses as $e)<tr><td>{{ $e->date->format('d/m/Y') }}</td><td>{{ $e->user?->name }}</td><td>{{ $cats[$e->category] ?? $e->category }}</td><td>{{ $e->description ?: 'Sem observação' }}</td><td class="text-right font-semibold">R$ {{ number_format($e->amount,2,',','.') }}</td><td class="text-right"><form method="POST" action="{{ route('manager.expenses.destroy',$e) }}" onsubmit="return confirm('Excluir despesa?')">@csrf @method('DELETE')<button class="cw-action-danger" aria-label="Remover despesa"><i class="fas fa-trash"></i></button></form></td></tr>
        @empty<tr><td colspan="6" class="text-center py-8 text-slate-500">Nenhuma despesa extra nesta competência.</td></tr>@endforelse
        </tbody></table></div><div class="p-4">{{ $expenses->links() }}</div></section>
    @else
        <section class="cw-card overflow-hidden"><div class="p-5 border-b border-slate-100"><h3 class="font-bold text-slate-900">Histórico de folhas fechadas</h3><p class="text-xs text-slate-500">Carregado somente ao abrir esta seção.</p></div><div class="overflow-x-auto"><table class="cw-table"><thead><tr><th>Competência</th><th>Colaborador</th><th class="text-right">Base</th><th class="text-right">Vales</th><th class="text-right">Consumos</th><th class="text-right">Líquido</th><th class="text-right">Outros custos</th><th class="text-right">Custo da empresa</th><th>Fechado em</th></tr></thead><tbody>
        @forelse($history as $h)<tr><td>{{ $h->period?->format('m/Y') }}</td><td>{{ $h->user?->name }}</td><td class="text-right">R$ {{ number_format($h->base_amount,2,',','.') }}</td><td class="text-right">R$ {{ number_format($h->advances_amount,2,',','.') }}</td><td class="text-right">R$ {{ number_format($h->consumptions_amount,2,',','.') }}</td><td class="text-right font-semibold">R$ {{ number_format($h->net_amount,2,',','.') }}</td><td class="text-right">R$ {{ number_format($h->extra_costs_amount,2,',','.') }}</td><td class="text-right">{{ $h->employer_cost_amount > 0 ? 'R$ '.number_format($h->employer_cost_amount,2,',','.') : '—' }}</td><td>{{ $h->closed_at?->format('d/m/Y') }}</td></tr>
        @empty<tr><td colspan="9" class="text-center py-10 text-slate-500">Sem fechamento registrado.</td></tr>@endforelse
        </tbody></table></div><div class="p-4">{{ $history->links() }}</div></section>
    @endif
</div>
@endsection
