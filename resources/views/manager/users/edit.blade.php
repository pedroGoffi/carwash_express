@extends('layouts.app')

@section('title', 'Editar Operador')
@section('header', 'Editar Operador')
@section('subheader', 'Altere as informações do operador')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6">
        
        <form action="{{ route('manager.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Usuário (Login) *</label>
                    <input type="text" name="username" value="{{ old('username', $user->name) }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nova Senha</label>
                    <input type="password" name="password" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <p class="text-xs text-gray-400 mt-1">Deixe em branco para manter a atual</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Nova Senha</label>
                    <input type="password" name="password_confirmation" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>

                <div>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="active" value="1" {{ $user->active ? 'checked' : '' }}>
                        <span class="text-sm text-gray-700">Usuário ativo</span>
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <a href="{{ route('manager.users.index') }}" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancelar</a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Atualizar</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection