<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'CarWash Express') }} · @yield('title', 'Sistema')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <meta name="theme-color" content="#ffffff">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="font-sans antialiased bg-[#F7F9FC] text-slate-900">
<div id="cw-progress" class="cw-progress" aria-hidden="true"></div>
<div x-data="{ mobileMenu:false }" class="min-h-screen">
    <div x-show="mobileMenu" x-cloak class="fixed inset-0 z-40 bg-slate-950/40 lg:hidden" @click="mobileMenu=false"></div>

    <aside :class="mobileMenu ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-72 bg-white text-slate-800 border-r border-slate-200 transition-transform lg:translate-x-0 flex flex-col">
        <div class="h-20 px-5 flex items-center gap-3 border-b border-slate-100">
            <div class="w-11 h-11 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-sm"><i class="fas fa-car-side"></i></div>
            <div><p class="font-bold text-lg leading-tight">CarWash Express</p><p class="text-xs text-slate-400">Gestão operacional</p></div>
        </div>

        <nav class="flex-1 overflow-y-auto p-4 space-y-6">
            @if(auth()->user()->isOperator() || auth()->user()->isAdmin() || auth()->user()->isManager())
                <div>
                    <p class="px-3 mb-2 text-[11px] font-bold uppercase tracking-[.18em] text-slate-400">Operação</p>
                    <div class="space-y-1">
                        @if(Route::has('operator.orders.index'))
                        <a href="{{ route('operator.orders.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl {{ request()->routeIs('operator.orders.index') || request()->routeIs('operator.orders.status') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-blue-700' }}"><i class="fas fa-rectangle-list w-5 text-center"></i><span class="font-semibold">Ordens</span></a>
                        @endif
                        @if(Route::has('operator.orders.create'))
                        <a href="{{ route('operator.orders.create') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl {{ request()->routeIs('operator.orders.create') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-blue-700' }}"><i class="fas fa-plus w-5 text-center"></i><span class="font-semibold">Nova ordem</span></a>
                        @endif
                        @if(Route::has('operator.services.to-deliver'))
                        <a href="{{ route('operator.services.to-deliver') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl {{ request()->routeIs('operator.services.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-blue-700' }}"><i class="fas fa-key w-5 text-center"></i><span class="font-semibold">Entregas</span></a>
                        @endif
                        @if(Route::has('operator.notes.index'))
                        <a href="{{ route('operator.notes.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl {{ request()->routeIs('operator.notes.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-blue-700' }}"><i class="fas fa-note-sticky w-5 text-center"></i><span class="font-semibold">Anotações</span></a>
                        @endif
                    </div>
                </div>
            @endif

            @if(auth()->user()->isManager() || auth()->user()->isAdmin())
                <div>
                    <p class="px-3 mb-2 text-[11px] font-bold uppercase tracking-[.18em] text-slate-400">Gestão</p>
                    <div class="space-y-1">
                        <a href="{{ route('manager.dashboard') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl {{ request()->routeIs('manager.dashboard*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-blue-700' }}"><i class="fas fa-chart-pie w-5 text-center"></i><span class="font-semibold">Dashboard</span></a>
                        <a href="{{ route('manager.products.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl {{ request()->routeIs('manager.products.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-blue-700' }}"><i class="fas fa-box w-5 text-center"></i><span class="font-semibold">Produtos</span></a>
                        <a href="{{ route('manager.users.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl {{ request()->routeIs('manager.users.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-blue-700' }}"><i class="fas fa-users w-5 text-center"></i><span class="font-semibold">Equipe</span></a>
                        <a href="{{ route('manager.payroll.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl {{ request()->routeIs('manager.payroll.*') || request()->routeIs('manager.advances.*') || request()->routeIs('manager.consumptions.*') || request()->routeIs('manager.expenses.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-blue-700' }}"><i class="fas fa-wallet w-5 text-center"></i><span class="font-semibold">Folha & custos</span></a>
                        <a href="{{ route('manager.notes.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl {{ request()->routeIs('manager.notes.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-blue-700' }}"><i class="fas fa-note-sticky w-5 text-center"></i><span class="font-semibold">Anotações</span></a>
                        <a href="{{ route('manager.reports.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl {{ request()->routeIs('manager.reports.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-blue-700' }}"><i class="fas fa-chart-line w-5 text-center"></i><span class="font-semibold">Relatórios</span></a>
                    </div>
                </div>
            @endif
        </nav>

        <div class="p-4 border-t border-slate-100">
            <div class="flex items-center gap-3 px-2">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center"><i class="fas fa-user"></i></div>
                <div class="min-w-0 flex-1"><p class="font-semibold text-sm truncate">{{ auth()->user()->name }}</p><p class="text-xs text-slate-500">{{ auth()->user()->role->value ?? auth()->user()->role }}</p></div>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="w-9 h-9 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-blue-700"><i class="fas fa-arrow-right-from-bracket"></i></button></form>
            </div>
        </div>
    </aside>

    <main class="lg:pl-72 min-h-screen">
        <header class="sticky top-0 z-30 h-20 bg-white/90 backdrop-blur border-b border-slate-200 px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            <div class="flex items-center gap-3 min-w-0"><button @click="mobileMenu=true" class="lg:hidden w-10 h-10 rounded-xl bg-slate-100"><i class="fas fa-bars"></i></button><div class="min-w-0"><h1 class="text-xl font-bold text-slate-900 truncate">@yield('header', 'CarWash Express')</h1><p class="text-sm text-slate-500 truncate hidden sm:block">@yield('subheader')</p></div></div>
            <div class="hidden sm:flex items-center gap-2 text-xs text-slate-500 bg-slate-100 rounded-xl px-3 py-2"><span class="w-2 h-2 rounded-full bg-emerald-500"></span>Sistema online</div>
        </header>
        <div class="p-4 sm:p-6 lg:p-8 max-w-[1600px] mx-auto">@yield('content')</div>
    </main>
</div>
</body>
</html>
