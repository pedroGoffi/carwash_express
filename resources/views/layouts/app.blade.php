<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CarWash Chameleon') }} - @yield('title', 'Operador')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        .menu-item {
            transition: all 0.2s ease;
        }
        .menu-item:hover {
            transform: translateX(8px);
        }
        .menu-active {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(59,130,246,0.3);
        }
        .menu-active i {
            color: white !important;
        }
        .card-hover {
            transition: all 0.2s ease;
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
    </style>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gradient-to-br from-gray-50 to-gray-100">
    <div class="flex h-screen">
        
        {{-- MENU LATERAL MODERNO --}}
        <aside class="w-72 bg-white shadow-2xl flex flex-col">
            {{-- Logo com gradiente --}}
            <div class="p-6 border-b bg-gradient-to-r from-blue-600 to-blue-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur">
                        <i class="fas fa-car-wash text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white">CarWash</h1>
                        <p class="text-xs text-blue-100">Sistema de Gestão</p>
                    </div>
                </div>
            </div>

            {{-- Informações do Usuário --}}
            @auth
            <div class="p-4 mx-3 mt-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center shadow-lg">
                        <i class="fas fa-user text-white text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-gray-800">{{ auth()->user()->name }}</p>
                        <div class="flex items-center gap-1 mt-0.5">
                            <i class="fas fa-circle text-blue-500 text-xs"></i>
                            <p class="text-xs text-gray-500">Operador</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-red-400 hover:text-red-600 transition">
                            <i class="fas fa-sign-out-alt text-lg"></i>
                        </button>
                    </form>
                </div>
            </div>
            @endauth

            {{-- MENU COM 3 COLUNAS VISUAIS --}}
            <nav class="flex-1 p-4 space-y-6 overflow-y-auto">
                @if (auth()->user()->isAdmin() || auth()->user()->isOperator())
                    {{-- COLUNA 1: OPERAÇÕES PRINCIPAIS --}}
                    <div>
                        <div class="flex items-center gap-2 mb-3 px-2">
                            <div class="w-1 h-5 bg-blue-500 rounded-full"></div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Operações</p>
                        </div>
                        <div class="space-y-2">
                            <a href="{{ route('operator.orders.create') }}" 
                            class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition-all
                            {{ request()->routeIs('operator.orders.*') ? 'menu-active' : 'bg-gray-50 text-gray-700 hover:bg-blue-50' }}">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ request()->routeIs('operator.orders.*') ? 'bg-white/20' : 'bg-blue-100' }}">
                                    <i class="fas fa-plus-circle {{ request()->routeIs('operator.orders.*') ? 'text-white' : 'text-blue-600' }} text-lg"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold">Nova Nota</p>
                                    <p class="text-xs opacity-70">Registrar serviço</p>
                                </div>
                                <i class="fas fa-chevron-right text-xs opacity-50"></i>
                            </a>
                            <a href="{{ route('operator.services.to-deliver') }}" 
                                class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition-all
                                {{ request()->routeIs('operator.services.to-deliver') ? 'menu-active' : 'bg-gray-50 text-gray-700 hover:bg-blue-50' }}"
                            >
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ request()->routeIs('operator.services.to-deliver') ? 'bg-white/20' : 'bg-purple-100' }}">
                                    <i class="fas fa-key {{ request()->routeIs('operator.services.to-deliver') ? 'text-white' : 'text-purple-600' }} text-lg"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold">Entregar Veículo</p>
                                    <p class="text-xs opacity-70">Devolver chaves</p>
                                </div>
                            </a>
                                    
                            <a href="{{ route('operator.notes.index') }}" 
                            class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition-all
                            {{ request()->routeIs('operator.notes.*') ? 'menu-active' : 'bg-gray-50 text-gray-700 hover:bg-blue-50' }}">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ request()->routeIs('operator.notes.*') ? 'bg-white/20' : 'bg-green-100' }}">
                                    <i class="fas fa-edit {{ request()->routeIs('operator.notes.*') ? 'text-white' : 'text-green-600' }} text-lg"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold">Anotações</p>
                                    <p class="text-xs opacity-70">Registros do dia</p>
                                </div>
                                <i class="fas fa-chevron-right text-xs opacity-50"></i>
                            </a>
                        </div>
                    </div>                
                @endif                

                {{-- COLUNA 2: GESTÃO (visível apenas para admin/gerente) --}}
                @if(auth()->user()->isManager() || auth()->user()->isAdmin())
                <div>
                    <div class="flex items-center gap-2 mb-3 px-2">
                        <div class="w-1 h-5 bg-purple-500 rounded-full"></div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Gestão</p>
                    </div>
                    <div class="space-y-2">
                        <a href="{{ route('manager.dashboard') }}" 
                        class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition-all
                        {{ request()->routeIs('manager.dashboard') ? 'menu-active' : 'bg-gray-50 text-gray-700 hover:bg-purple-50' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ request()->routeIs('manager.dashboard') ? 'bg-white/20' : 'bg-purple-100' }}">
                                <i class="fas fa-chart-line {{ request()->routeIs('manager.dashboard') ? 'text-white' : 'text-purple-600' }} text-lg"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold">Dashboard</p>
                                <p class="text-xs opacity-70">Visão geral</p>
                            </div>
                            <i class="fas fa-chevron-right text-xs opacity-50"></i>
                        </a>

                        <a href="{{ route('manager.products.index') }}" 
                        class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition-all
                        {{ request()->routeIs('manager.products.*') ? 'menu-active' : 'bg-gray-50 text-gray-700 hover:bg-purple-50' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ request()->routeIs('manager.products.*') ? 'bg-white/20' : 'bg-purple-100' }}">
                                <i class="fas fa-box {{ request()->routeIs('manager.products.*') ? 'text-white' : 'text-purple-600' }} text-lg"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold">Produtos</p>
                                <p class="text-xs opacity-70">Serviços e itens</p>
                            </div>
                            <i class="fas fa-chevron-right text-xs opacity-50"></i>
                        </a>

                        <a href="{{ route('manager.users.index') }}" 
                        class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition-all
                        {{ request()->routeIs('manager.users.*') ? 'menu-active' : 'bg-gray-50 text-gray-700 hover:bg-purple-50' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ request()->routeIs('manager.users.*') ? 'bg-white/20' : 'bg-purple-100' }}">
                                <i class="fas fa-users {{ request()->routeIs('manager.users.*') ? 'text-white' : 'text-purple-600' }} text-lg"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold">Operadores</p>
                                <p class="text-xs opacity-70">Funcionários</p>
                            </div>
                            <i class="fas fa-chevron-right text-xs opacity-50"></i>
                        </a>

                        <a href="{{ route('manager.reports.index') }}" 
                        class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition-all
                        {{ request()->routeIs('manager.reports.*') ? 'menu-active' : 'bg-gray-50 text-gray-700 hover:bg-purple-50' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ request()->routeIs('manager.reports.*') ? 'bg-white/20' : 'bg-purple-100' }}">
                                <i class="fas fa-file-alt {{ request()->routeIs('manager.reports.*') ? 'text-white' : 'text-purple-600' }} text-lg"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold">Relatórios</p>
                                <p class="text-xs opacity-70">Análise de dados</p>
                            </div>
                            <i class="fas fa-chevron-right text-xs opacity-50"></i>
                        </a>
                    </div>
                </div>
                @endif

                @if(auth()->user()->isManager())
                    {{-- Financeiro --}}
                    <div>
                        <div class="flex items-center gap-2 mb-3 px-2">
                            <div class="w-1 h-5 bg-green-500 rounded-full"></div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Financeiro</p>
                        </div>
                        <div class="space-y-2">
                            <a href="{{ route('manager.advances.index') }}" 
                            class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition-all
                            {{ request()->routeIs('manager.advances.*') ? 'menu-active' : 'bg-gray-50 text-gray-700 hover:bg-green-50' }}">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ request()->routeIs('manager.advances.*') ? 'bg-white/20' : 'bg-green-100' }}">
                                    <i class="fas fa-hand-holding-usd {{ request()->routeIs('manager.advances.*') ? 'text-white' : 'text-green-600' }} text-lg"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold">Vales</p>
                                    <p class="text-xs opacity-70">Controle de adiantamentos</p>
                                </div>
                            </a>

                            <a href="{{ route('manager.consumptions.index') }}" 
                                class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition-all
                                {{ request()->routeIs('manager.consumptions.*') ? 'menu-active' : 'bg-gray-50 text-gray-700 hover:bg-orange-50' }}">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ request()->routeIs('manager.consumptions.*') ? 'bg-white/20' : 'bg-orange-100' }}">
                                        <i class="fas fa-coffee {{ request()->routeIs('manager.consumptions.*') ? 'text-white' : 'text-orange-600' }} text-lg"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-semibold">Consumo</p>
                                        <p class="text-xs opacity-70">Refrigerantes, lanches</p>
                                    </div>
                                </a>

                            <a href="{{ route('manager.payroll.index') }}" 
                            class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition-all
                            {{ request()->routeIs('manager.payroll.*') ? 'menu-active' : 'bg-gray-50 text-gray-700 hover:bg-green-50' }}">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ request()->routeIs('manager.payroll.*') ? 'bg-white/20' : 'bg-green-100' }}">
                                    <i class="fas fa-calculator {{ request()->routeIs('manager.payroll.*') ? 'text-white' : 'text-green-600' }} text-lg"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold">Folha de Pagamento</p>
                                    <p class="text-xs opacity-70">Salários e descontos</p>
                                </div>
                            </a>
                        </div>
                    </div>
                @endif

                {{-- COLUNA 3: ADMIN (apenas admin) --}}
                @if(auth()->user()->isAdmin())
                <div>
                    <div class="flex items-center gap-2 mb-3 px-2">
                        <div class="w-1 h-5 bg-red-500 rounded-full"></div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Administração</p>
                    </div>
                    <div class="space-y-2">
                        <div class="bg-gray-50 rounded-xl p-4 opacity-60 cursor-not-allowed">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-gray-200 flex items-center justify-center">
                                    <i class="fas fa-building text-gray-400 text-lg"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-500">Empresas</p>
                                    <p class="text-xs text-gray-400">Em breve</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4 opacity-60 cursor-not-allowed">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-gray-200 flex items-center justify-center">
                                    <i class="fas fa-cog text-gray-400 text-lg"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-500">Configurações</p>
                                    <p class="text-xs text-gray-400">Em breve</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                
                @if (auth()->user()->isOperator())
                    {{-- DICA RÁPIDA --}}
                    <div class="mt-6 p-3 bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl border border-yellow-200">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-lightbulb text-yellow-500 text-lg"></i>
                            <p class="text-xs text-gray-600">Dica: Selecione o tipo do veículo primeiro para ver os preços ajustados</p>
                        </div>
                    </div>                                
                @endif
            </nav>
        </aside>

        {{-- CONTEÚDO PRINCIPAL --}}
        <main class="flex-1 overflow-y-auto">
            {{-- Header --}}
            <header class="bg-white shadow-sm sticky top-0 z-10">
                <div class="px-8 py-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-{{ request()->routeIs('operator.orders.*') ? 'plus-circle' : (request()->routeIs('operator.notes.*') ? 'edit' : 'chart-line') }} text-blue-600 text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800">
                                @yield('header', 'Painel do Operador')
                            </h1>
                            <p class="text-sm text-gray-400 mt-0.5">@yield('subheader', 'Gerencie as operações do dia')</p>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Flash Messages --}}
            @if(session('success'))
            <div class="px-8 pt-6">
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg flex justify-between items-center shadow-sm">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-check-circle text-green-600 text-lg"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">&times;</button>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="px-8 pt-6">
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg flex justify-between items-center shadow-sm">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-exclamation-circle text-red-600 text-lg"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900">&times;</button>
                </div>
            </div>
            @endif

            @if($errors->any())
            <div class="px-8 pt-6">
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-sm">
                    @foreach($errors->all() as $error)
                        <p class="flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> {{ $error }}</p>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Content --}}
            <div class="p-8">
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>