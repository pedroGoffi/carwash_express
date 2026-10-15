@extends('layouts.app')
@section('title', 'Anotações')
@section('header', 'Anotações da equipe')
@section('subheader', 'Registros e solicitações da operação')
@section('content')
<div class="space-y-4">
    @if(session('success'))<div class="rounded-xl bg-emerald-50 p-4 text-emerald-800">{{ session('success') }}</div>@endif
    @forelse($notes as $note)
      <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap gap-2 items-center mb-2"><span class="text-sm font-semibold text-slate-900">{{ $note->user?->name ?? 'Colaborador' }}</span>
              @if($note->is_pinned)<span class="text-xs rounded-full bg-blue-50 text-blue-700 px-2 py-1">Fixada</span>@endif
              @if($note->type === 'cancel_request')<span class="text-xs rounded-full bg-amber-50 text-amber-700 px-2 py-1">Solicitação de cancelamento</span>@endif
            </div>
            <p class="whitespace-pre-wrap break-words text-sm text-slate-700">{{ $note->content }}</p>
            <p class="text-xs text-slate-400 mt-3">{{ $note->created_at?->format('d/m/Y H:i') }}</p>
          </div>
          <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('manager.notes.pin', $note) }}">@csrf<button class="text-sm rounded-lg border border-slate-200 px-3 py-2 hover:bg-slate-50">{{ $note->is_pinned ? 'Desfixar' : 'Fixar' }}</button></form>
            <form method="POST" action="{{ route('manager.notes.destroy', $note) }}" onsubmit="return confirm('Excluir anotação?')">@csrf @method('DELETE')<button class="text-sm rounded-lg border border-red-100 text-red-700 px-3 py-2 hover:bg-red-50">Excluir</button></form>
          </div>
        </div>
      </article>
    @empty
      <div class="rounded-2xl border border-slate-200 bg-white p-12 text-center text-slate-500">Nenhuma anotação registrada.</div>
    @endforelse
</div>
@endsection
