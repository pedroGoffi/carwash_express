<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index()
    {
        $notes = Note::where('user_id', auth()->id())
            ->where('type', 'note')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('operator.notes.index', compact('notes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'note_content' => 'required|string|max:500',
        ]);

        Note::create([
            'company_id' => auth()->user()->company_id,
            'user_id' => auth()->id(),
            'content' => $request->note_content,
            'type' => 'note',
        ]);

        return redirect()->route('operator.notes.index')
            ->with('success', 'Anotação salva!');
    }

    public function destroy(Note $note)
    {
        if ($note->user_id !== auth()->id()) {
            abort(403);
        }
        
        $note->delete();
        
        return redirect()->route('operator.notes.index')
            ->with('success', 'Anotação removida!');
    }
}