@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-edit mr-2"></i> Minhas Anotações
        </h1>
        <button onclick="openNoteModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i> Nova Anotação
        </button>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b">
            <h2 class="font-semibold">Anotações do dia</h2>
        </div>
        
        <div class="divide-y">
            @forelse($notes as $note)
            <div class="p-4 hover:bg-gray-50">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <div class="text-xs text-gray-400 mb-1">
                            <i class="fas fa-clock mr-1"></i> {{ $note->created_at->format('d/m/Y H:i') }}
                        </div>
                        <p class="text-gray-700">{{ $note->content }}</p>
                    </div>
                    <form action="{{ route('operator.notes.destroy', $note) }}" method="POST" onsubmit="return confirm('Excluir anotação?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-gray-500">
                <i class="fas fa-edit text-4xl mb-2"></i>
                <p>Nenhuma anotação ainda.</p>
                <p class="text-sm">Clique em "Nova Anotação" para adicionar.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- MODAL NOVA ANOTAÇÃO --}}
<div id="noteModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold">Nova Anotação</h3>
            <button onclick="closeNoteModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>

        <form action="{{ route('operator.notes.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <textarea name="note_content" required rows="4" class="w-full border rounded-lg px-3 py-2" placeholder="Digite sua anotação..."></textarea>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeNoteModal()" class="px-4 py-2 border rounded hover:bg-gray-100">Cancelar</button>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Salvar</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openNoteModal() {
        document.getElementById('noteModal').classList.remove('hidden');
    }

    function closeNoteModal() {
        document.getElementById('noteModal').classList.add('hidden');
    }
</script>
@endsection