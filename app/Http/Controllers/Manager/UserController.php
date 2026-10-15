<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()->where('company_id', auth()->user()->company_id)
            ->where('role', UserRole::OPERATOR->value)
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'ilike', '%'.str_replace(['%', '_'], ['\%', '\_'], trim($request->input('q'))).'%'))
            ->orderBy('name')->paginate(25)->withQueryString();
        return view('manager.users.index', compact('users'));
    }

    public function create() { return view('manager.users.create'); }

    public function store(Request $request)
    {
        $data = $this->validated($request, true);
        $data['company_id'] = auth()->user()->company_id;
        $data['role'] = UserRole::OPERATOR;
        $data['password'] = Hash::make($data['password']);
        $data['active'] = $request->boolean('active', true);
        User::create($data);
        return redirect()->route('manager.users.index')->with('success', 'Colaborador cadastrado.');
    }

    public function edit(User $user)
    {
        $this->authorizeCompany($user);
        return view('manager.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeCompany($user);
        $data = $this->validated($request, false, $user);
        $data['active'] = $request->boolean('active');
        if (empty($data['password'])) unset($data['password']);
        else $data['password'] = Hash::make($data['password']);
        $user->update($data);
        return redirect()->route('manager.users.index')->with('success', 'Cadastro do colaborador atualizado.');
    }

    public function destroy(User $user)
    {
        $this->authorizeCompany($user);
        // Nunca apagar histórico financeiro / vínculo de OS acidentalmente.
        $user->update(['active' => false]);
        return redirect()->route('manager.users.index')->with('success', 'Colaborador desativado. Histórico preservado.');
    }

    private function validated(Request $request, bool $create, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('users', 'name')->where('company_id', auth()->user()->company_id)->ignore($user?->id)],
            'password' => [$create ? 'required' : 'nullable', 'string', 'min:8', 'confirmed'],
            'phone' => 'nullable|string|max:32',
            'email_contact' => 'nullable|email|max:255',
            'document' => 'nullable|string|max:32',
            'job_title' => 'nullable|string|max:100',
            'employment_type' => ['nullable', Rule::in(['clt','daily','contract','other'])],
            'pix_key' => 'nullable|string|max:255',
            'staff_notes' => 'nullable|string|max:3000',
            'monthly_salary' => 'nullable|numeric|min:0|max:9999999',
            'daily_rate' => 'nullable|numeric|min:0|max:9999999',
            'days_worked' => 'nullable|integer|min:0|max:31',
            'hired_at' => 'nullable|date',
            'active' => 'sometimes|boolean',
        ]);
    }

    private function authorizeCompany(User $user): void
    {
        abort_unless($user->company_id === auth()->user()->company_id && $user->role === UserRole::OPERATOR, 403);
    }
}
