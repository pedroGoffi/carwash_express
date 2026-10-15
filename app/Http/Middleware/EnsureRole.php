<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$allowed): Response
    {
        $user = $request->user();
        if (!$user) {
            abort(401);
        }

        $role = $user->role;
        $roleValue = $role instanceof \BackedEnum ? $role->value : (string) $role;
        abort_unless(in_array($roleValue, $allowed, true), 403, 'Perfil sem acesso a este módulo.');

        return $next($request);
    }
}
