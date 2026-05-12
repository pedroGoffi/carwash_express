<?php

namespace App\Providers;

use App\Enums\Permission;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Blade directive for permissions
        Blade::if('can', function ($permission) {
            return auth()->check() && auth()->user()->hasPermission($permission);
        });
        
        Blade::if('cannot', function ($permission) {
            return !auth()->check() || !auth()->user()->hasPermission($permission);
        });
        
        Blade::if('role', function (UserRole $role) {
            return auth()->check() && auth()->user()->role->name === $role;
        });
    }
}