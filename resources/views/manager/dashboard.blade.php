@extends('layouts.app')

@section('title', 'Dashboard Gerencial')
@section('header', 'Dashboard')
@section('subheader', 'Visão completa da operação')

@section('content')
<div class="space-y-6">
    
    {{-- ========== PRIMEIRA LINHA: CARDS PRINCIPAIS ========== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        {{-- Card: Ordens Hoje --}}
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden group">
            <div class="p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center group-hover:bg-blue-500 transition-colors duration-300">
                        <i class="fas fa-clipboard-list text-blue-600 text-xl group-hover:text-white transition-colors duration-300"></i>
                    </div>
                    <span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded-full">Hoje</span>
                </div>
                <div>
                    <p class="text-3xl font-bold text-gray-800">{{ number_format($totalOrdersToday ?? 0) }}</p>
                    <p class="text-sm text-gray-500 mt-1">Ordens de serviço</p>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-400">Fila: {{ number_format($queueCount ?? 0) }}</span>
                        <span class="text-gray-400">Finalizadas: {{ number_format(($totalOrdersToday ?? 0) - ($queueCount ?? 0)) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card: Faturamento Hoje --}}
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden group">
            <div class="p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center group-hover:bg-green-500 transition-colors duration-300">
                        <i class="fas fa-dollar-sign text-green-600 text-xl group-hover:text-white transition-colors duration-300"></i>
                    </div>
                    <span class="text-xs text-green-600 bg-green-50 px-2 py-1 rounded-full">↑ Hoje</span>
                </div>
                <div>
                    <p class="text-3xl font-bold text-green-600">R$ {{ number_format($totalRevenueToday ?? 0, 2, ',', '.') }}</p>
                    <p class="text-sm text-gray-500 mt-1">Faturamento do dia</p>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-400">Ticket médio: R$ {{ number_format($averageTicket ?? 0, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card: Clientes Ativos --}}
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden group">
            <div class="p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center group-hover:bg-purple-500 transition-colors duration-300">
                        <i class="fas fa-users text-purple-600 text-xl group-hover:text-white transition-colors duration-300"></i>
                    </div>
                    <span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded-full">Últimos 30 dias</span>
                </div>
                <div>
                    <p class="text-3xl font-bold text-gray-800">{{ number_format($totalCustomers ?? 0) }}</p>
                    <p class="text-sm text-gray-500 mt-1">Clientes ativos</p>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-400">PJ: {{ number_format($totalCorporateCustomers ?? 0) }}</span>
                        <span class="text-gray-400">PF: {{ number_format(($totalCustomers ?? 0) - ($totalCorporateCustomers ?? 0)) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card: Produtos Ativos --}}
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden group">
            <div class="p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center group-hover:bg-orange-500 transition-colors duration-300">
                        <i class="fas fa-box text-orange-600 text-xl group-hover:text-white transition-colors duration-300"></i>
                    </div>
                    <span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded-full">Ativos</span>
                </div>
                <div>
                    <p class="text-3xl font-bold text-gray-800">{{ number_format($totalProducts ?? 0) }}</p>
                    <p class="text-sm text-gray-500 mt-1">Produtos disponíveis</p>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-400">Lavagens: {{ number_format($totalWashes ?? 0) }}</span>
                        <span class="text-gray-400">Adicionais: {{ number_format(($totalProducts ?? 0) - ($totalWashes ?? 0)) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========== SEGUNDA LINHA: FINANCEIRO ========== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        
        {{-- Card: Gastos com Colaboradores --}}
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 border-l-4 border-red-500">
            <div class="p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-hand-holding-usd text-red-600 text-xl"></i>
                    </div>
                    <span class="text-xs text-red-600 bg-red-50 px-2 py-1 rounded-full">Este mês</span>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mt-1">Gastos com Colaboradores</p>
                    <p class="text-3xl font-bold text-red-600">R$ {{ number_format(($totalSalaries ?? 0) + ($totalAdvances ?? 0) + ($totalConsumptions ?? 0), 2, ',', '.') }}</p>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <div class="grid grid-cols-3 gap-2 text-xs">
                        <div class="text-center">
                            <p class="text-gray-400">Salários</p>
                            <p class="font-semibold text-gray-700">R$ {{ number_format($totalSalaries ?? 0, 2, ',', '.') }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-gray-400">Vales</p>
                            <p class="font-semibold text-gray-700">R$ {{ number_format($totalAdvances ?? 0, 2, ',', '.') }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-gray-400">Consumo</p>
                            <p class="font-semibold text-gray-700">R$ {{ number_format($totalConsumptions ?? 0, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card: Faturamento do Mês --}}
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 border-l-4 border-blue-500">
            <div class="p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-chart-line text-blue-600 text-xl"></i>
                    </div>
                    <span class="text-xs text-blue-600 bg-blue-50 px-2 py-1 rounded-full">Este mês</span>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mt-1">Faturamento do Mês</p>
                    <p class="text-3xl font-bold text-blue-600">R$ {{ number_format($totalRevenueMonth ?? 0, 2, ',', '.') }}</p>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-400">Ordens: {{ number_format($totalOrdersMonth ?? 0) }}</span>
                        <span class="text-gray-400">Ticket médio: R$ {{ number_format($averageTicketMonth ?? 0, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card: Lucro Líquido --}}
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300">
            <div class="p-5 text-white">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-chart-pie text-white text-xl"></i>
                    </div>
                    <span class="text-xs text-white/80 bg-white/20 px-2 py-1 rounded-full">Margem: {{ number_format($profitMargin ?? 0, 1) }}%</span>
                </div>
                <div>
                    <p class="text-sm text-white/80 mt-1">Lucro Líquido</p>
                    <p class="text-3xl font-bold text-white">R$ {{ number_format($totalProfit ?? 0, 2, ',', '.') }}</p>
                </div>
                <div class="mt-3 pt-3 border-t border-white/20">
                    <div class="flex justify-between text-xs">
                        <span class="text-white/70">Receita: R$ {{ number_format($totalRevenueMonth ?? 0, 2, ',', '.') }}</span>
                        <span class="text-white/70">Gastos: -R$ {{ number_format(($totalSalaries ?? 0) + ($totalAdvances ?? 0) + ($totalConsumptions ?? 0), 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========== TERCEIRA LINHA: GRÁFICOS ========== --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        {{-- Gráfico de Faturamento --}}
        <div class="bg-white rounded-2xl shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <h3 class="font-semibold text-gray-800">
                        <i class="fas fa-chart-line text-blue-500 mr-2"></i> Evolução do Faturamento
                    </h3>
                    <div class="flex gap-2">
                        <button onclick="changeChartPeriod('week')" class="period-btn active px-3 py-1 text-xs rounded-lg bg-blue-50 text-blue-600 font-medium">Semana</button>
                        <button onclick="changeChartPeriod('month')" class="period-btn px-3 py-1 text-xs rounded-lg text-gray-500 hover:bg-gray-100 font-medium">Mês</button>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <canvas id="revenueChart" height="280"></canvas>
            </div>
        </div>

        {{-- Gráfico de Receita vs Despesa --}}
        <div class="bg-white rounded-2xl shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">
                    <i class="fas fa-chart-bar text-green-500 mr-2"></i> Receita vs Despesa
                </h3>
                <div class="flex gap-4 mt-2">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        <span class="text-xs text-gray-500">Receita</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                        <span class="text-xs text-gray-500">Despesa</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                        <span class="text-xs text-gray-500">Lucro</span>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <canvas id="profitChart" height="280"></canvas>
            </div>
        </div>
    </div>

    {{-- ========== QUARTA LINHA: RANKINGS E TABELAS ========== --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        {{-- Ranking de Produtos --}}
        <div class="bg-white rounded-2xl shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">
                    <i class="fas fa-trophy text-yellow-500 mr-2"></i> Produtos Mais Vendidos
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-5">
                    @forelse($topProducts ?? [] as $index => $product)
                    <div class="group">
                        <div class="flex justify-between items-center mb-1">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-full 
                                    {{ $index == 0 ? 'bg-yellow-500' : ($index == 1 ? 'bg-gray-400' : ($index == 2 ? 'bg-orange-500' : 'bg-gray-300')) }} 
                                    flex items-center justify-center text-white text-xs font-bold group-hover:scale-110 transition-transform">
                                    {{ $index + 1 }}
                                </div>
                                <span class="font-medium text-gray-700">{{ $product->name }}</span>
                            </div>
                            <span class="font-bold text-gray-800">{{ $product->total }} vendas</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                            <div class="bg-blue-500 h-2.5 rounded-full transition-all duration-500 group-hover:bg-blue-600" 
                                 style="width: {{ ($product->total / ($topProducts->first()->total ?? 1)) * 100 }}%"></div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-10 text-gray-400">
                        <i class="fas fa-chart-simple text-4xl mb-3"></i>
                        <p>Nenhum dado disponível</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Últimas Ordens --}}
        <div class="bg-white rounded-2xl shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">
                    <i class="fas fa-history text-blue-500 mr-2"></i> Últimas Atividades
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">OS</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($recentOrders ?? [] as $order)
                        <tr class="hover:bg-gray-50 transition-all duration-200">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-sm font-bold text-blue-600">#{{ $order->code }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-600">{{ $order->customer_name ?? 'Cliente não identificado' }}</td>
                            <td class="px-6 py-3 text-right text-sm font-semibold text-green-600">R$ {{ number_format($order->total, 2, ',', '.') }}</td>
                            <td class="px-6 py-3 text-center">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    {{ $order->status == 'queued' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                    <span class="w-1.5 h-1.5 rounded-full mr-1 {{ $order->status == 'queued' ? 'bg-yellow-500 animate-pulse' : 'bg-green-500' }}"></span>
                                    {{ $order->status == 'queued' ? 'Na Fila' : 'Finalizado' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-400">
                                <i class="fas fa-inbox text-3xl mb-2"></i>
                                <p>Nenhuma ordem registrada</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-3 border-t border-gray-100 bg-gray-50 rounded-b-2xl">
                <a href="#" class="text-xs text-blue-600 hover:text-blue-800 flex items-center gap-1 font-medium">
                    Ver todas as ordens <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- ========== QUINTA LINHA: RESUMO DO DIA ========== --}}
    <div class="bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-600 rounded-2xl shadow-lg overflow-hidden">
        <div class="px-6 py-6">
            <div class="flex flex-col lg:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center">
                        <i class="fas fa-calendar-day text-white text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-white/80 text-sm">Resumo Operacional</p>
                        <p class="text-white text-xl font-bold">{{ now()->format('l, d \\d\\e F \\d\\e Y') }}</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-8">
                    <div class="text-center">
                        <p class="text-white/70 text-xs uppercase tracking-wide">Ordens</p>
                        <p class="text-white text-3xl font-bold">{{ $totalOrdersToday ?? 0 }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-white/70 text-xs uppercase tracking-wide">Faturamento</p>
                        <p class="text-white text-3xl font-bold">R$ {{ number_format($totalRevenueToday ?? 0, 2, ',', '.') }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-white/70 text-xs uppercase tracking-wide">Ticket Médio</p>
                        <p class="text-white text-3xl font-bold">R$ {{ number_format($averageTicket ?? 0, 2, ',', '.') }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-white/70 text-xs uppercase tracking-wide">Na Fila</p>
                        <p class="text-white text-3xl font-bold">{{ $queueCount ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let revenueChart, profitChart;

    // Gráfico de Faturamento
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($revenueByDay->pluck('date') ?? []) !!},
            datasets: [{
                label: 'Faturamento (R$)',
                data: {!! json_encode($revenueByDay->pluck('total') ?? []) !!},
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.05)',
                borderWidth: 3,
                pointRadius: 4,
                pointBackgroundColor: '#3b82f6',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'R$ ' + context.raw.toFixed(2).replace('.', ',');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toFixed(0);
                        }
                    }
                }
            }
        }
    });

    // Gráfico de Receita vs Despesa
    const profitCtx = document.getElementById('profitChart').getContext('2d');
    profitChart = new Chart(profitCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartMonths ?? []) !!},
            datasets: [
                {
                    label: 'Receita',
                    data: {!! json_encode($chartRevenue ?? []) !!},
                    backgroundColor: 'rgba(34, 197, 94, 0.7)',
                    borderColor: '#22c55e',
                    borderWidth: 1,
                    borderRadius: 8,
                    barPercentage: 0.7
                },
                {
                    label: 'Despesa',
                    data: {!! json_encode($chartExpenses ?? []) !!},
                    backgroundColor: 'rgba(239, 68, 68, 0.7)',
                    borderColor: '#ef4444',
                    borderWidth: 1,
                    borderRadius: 8,
                    barPercentage: 0.7
                },
                {
                    label: 'Lucro',
                    data: {!! json_encode($chartProfit ?? []) !!},
                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                    borderColor: '#3b82f6',
                    borderWidth: 2,
                    borderRadius: 8,
                    type: 'line',
                    tension: 0.3,
                    fill: false,
                    pointRadius: 4,
                    pointBackgroundColor: '#3b82f6'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': R$ ' + context.raw.toFixed(2).replace('.', ',');
                        }
                    }
                },
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 10
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toFixed(0);
                        }
                    },
                    grid: {
                        color: '#e5e7eb'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    function changeChartPeriod(period) {
        document.querySelectorAll('.period-btn').forEach(btn => {
            btn.classList.remove('bg-blue-50', 'text-blue-600');
            btn.classList.add('text-gray-500');
        });
        event.target.classList.add('bg-blue-50', 'text-blue-600');
        
        // AJAX para buscar dados do período
        fetch(`/manager/dashboard/chart-data?period=${period}`)
            .then(response => response.json())
            .then(data => {
                revenueChart.data.labels = data.labels;
                revenueChart.data.datasets[0].data = data.values;
                revenueChart.update();
            });
    }
</script>
@endsection