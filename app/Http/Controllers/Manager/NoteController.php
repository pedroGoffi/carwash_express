<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id;
        
        $notes = Note::where('company_id', $companyId)
            ->with('user')
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('manager.notes.index', compact('notes'));
    }

    public function destroy(Note $note)
    {
        if ($note->company_id !== auth()->user()->company_id) {
            abort(403);
        }
        
        $note->delete();
        
        return redirect()->route('manager.notes.index')
            ->with('success', 'Anotação removida!');
    }
    
    public function togglePin(Note $note)
    {
        if ($note->company_id !== auth()->user()->company_id) {
            abort(403);
        }
        
        $note->update(['is_pinned' => !$note->is_pinned]);
        
        return redirect()->route('manager.notes.index')
            ->with('success', $note->is_pinned ? 'Anotação fixada!' : 'Anotação desafixada!');
    }
}