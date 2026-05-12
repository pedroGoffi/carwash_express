@extends('layouts.app')

@section('title', 'Novo Operador')
@section('header', 'Novo Operador')
@section('subheader', 'Cadastre um novo operador')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6">
        
        <form action="{{ route('manager.users.store') }}" method="POST">
            @csrf
            
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Usuário (Login) *</label>
                    <input type="text" name="username" value="{{ old('username') }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">Nome usado para fazer login</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Senha *</label>
                    <input type="password" name="password" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Senha *</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <a href="{{ route('manager.users.index') }}" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancelar</a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Salvar</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection