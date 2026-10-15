@extends('layouts.app')
@section('title','Equipe')
@section('header','Equipe')
@section('subheader','Funcionários, informações e remuneração organizados')
@section('content')
<div class="space-y-5">
    @if(session('success'))<div class="cw-message" role="status">{{ session('success') }}</div>@endif
    <div class="cw-card p-4 flex flex-col sm:flex-row gap-3 justify-between sm:items-center">
        <form class="flex items-center gap-2 w-full sm:max-w-md" method="GET"><input class="cw-input" type="search" name="q" value="{{ request('q') }}" placeholder="Buscar pelo nome"><button class="cw-btn-secondary" type="submit">Buscar</button></form>
        <a href="{{ route('manager.users.create') }}" class="cw-btn-primary"><i class="fas fa-user-plus mr-2"></i>Novo funcionário</a>
    </div>
    <div class="cw-card overflow-hidden"><div class="overflow-x-auto"><table class="cw-table"><thead><tr><th>Funcionário</th><th>Função / vínculo</th><th>Salário base</th><th>Situação</th><th class="text-right">Ações</th></tr></thead><tbody>
    @forelse($users as $user)
    <tr><td><div class="font-semibold text-slate-900">{{ $user->name }}</div><div class="text-xs text-slate-500">{{ $user->phone ?: ($user->email_contact ?: 'Sem contato cadastrado') }}</div></td>
    <td><div>{{ $user->job_title ?: 'Operador' }}</div><div class="text-xs text-slate-500">{{ ['clt'=>'CLT','daily'=>'Diarista','contract'=>'Contrato','other'=>'Outro'][$user->employment_type] ?? 'Não informado' }}</div></td>
    <td class="whitespace-nowrap">{{ (float)$user->daily_rate > 0 ? 'R$ '.number_format($user->daily_rate,2,',','.').'/dia' : 'R$ '.number_format($user->monthly_salary,2,',','.') . '/mês' }}</td>
    <td><span class="cw-pill {{ $user->active ? 'cw-pill-success' : 'cw-pill-muted' }}">{{ $user->active ? 'Ativo' : 'Inativo' }}</span></td>
    <td class="text-right whitespace-nowrap"><a class="cw-link mr-3" href="{{ route('manager.payroll.index',['user_id'=>$user->id]) }}">Custos</a><a class="cw-link mr-3" href="{{ route('manager.users.edit',$user) }}">Editar</a>@if($user->active)<form class="inline" action="{{ route('manager.users.destroy',$user) }}" method="POST" onsubmit="return confirm('Desativar este colaborador? O histórico será preservado.')">@csrf @method('DELETE')<button class="text-rose-600 font-semibold text-sm hover:underline">Desativar</button></form>@endif</td></tr>
    @empty<tr><td colspan="5" class="py-12 text-center text-slate-500">Nenhum funcionário encontrado.</td></tr>@endforelse
    </tbody></table></div><div class="p-4">{{ $users->links() }}</div></div>
</div>
@endsection
