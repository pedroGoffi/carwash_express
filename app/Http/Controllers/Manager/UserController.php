<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where('company_id', auth()->user()->company_id)
            ->where('role', 'operator')
            ->orderBy('name')
            ->get();
            
        return view('manager.users.index', compact('users'));
    }

    public function create()
    {
        return view('manager.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|min:6|confirmed',
        ]);

        User::create([
            'company_id' => auth()->user()->company_id,
            'name' => $request->name,
            'password' => Hash::make($request->password),
            'role' => UserRole::OPERATOR,
        ]);

        return redirect()->route('manager.users.index')
            ->with('success', 'Operador criado!');
    }

    public function edit(User $user)
    {
        $this->authorizeCompany($user);
        return view('manager.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeCompany($user);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'nullable|min:6|confirmed',
        ]);

        $data = ['name' => $request->name];
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        
        $user->update($data);

        return redirect()->route('manager.users.index')
            ->with('success', 'Operador atualizado!');
    }

    public function destroy(User $user)
    {
        $this->authorizeCompany($user);
        $user->delete();
        
        return redirect()->route('manager.users.index')
            ->with('success', 'Operador removido!');
    }

    private function authorizeCompany($user)
    {
        if ($user->company_id !== auth()->user()->company_id) {
            abort(403);
        }
    }
}