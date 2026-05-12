@extends('layouts.app')

@section('title', 'Relatórios')
@section('header', 'Relatórios Gerenciais')
@section('subheader', 'Análise completa da operação')

@section('content')
<div class="space-y-6">
    
    {{-- Filtro de Datas --}}
    <div class="bg-white rounded-2xl shadow-sm p-5">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-calendar-alt text-blue-600 text-lg"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">Período de análise</h3>
                    <p class="text-xs text-gray-400">Selecione o intervalo para filtrar os dados</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <button onclick="setPeriod('today')" class="period-btn px-4 py-2 text-sm rounded-lg bg-gray-100 text-gray-600 hover:bg-blue-100 hover:text-blue-600 transition">Hoje</button>
                <button onclick="setPeriod('yesterday')" class="period-btn px-4 py-2 text-sm rounded-lg bg-gray-100 text-gray-600 hover:bg-blue-100 hover:text-blue-600 transition">Ontem</button>
                <button onclick="setPeriod('week')" class="period-btn px-4 py-2 text-sm rounded-lg bg-gray-100 text-gray-600 hover:bg-blue-100 hover:text-blue-600 transition">Esta semana</button>
                <button onclick="setPeriod('month')" class="period-btn px-4 py-2 text-sm rounded-lg bg-gray-100 text-gray-600 hover:bg-blue-100 hover:text-blue-600 transition">Este mês</button>
                <button onclick="setPeriod('last6months')" class="period-btn px-4 py-2 text-sm rounded-lg bg-gray-100 text-gray-600 hover:bg-blue-100 hover:text-blue-600 transition">Últimos 6 meses</button>
                <button onclick="openCustomDateModal()" class="px-4 py-2 text-sm rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition">
                    <i class="fas fa-calendar-plus mr-1"></i> Personalizado
                </button>
            </div>
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100 flex justify-between items-center">
            <div class="text-sm text-gray-500">
                <i class="fas fa-chart-line mr-1"></i> Período atual: 
                <span id="period-label" class="font-semibold text-gray-700">Últimos 30 dias</span>
            </div>
            <div class="text-sm text-gray-500">
                <i class="fas fa-sync-alt mr-1"></i> Última atualização: {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>

    {{-- Cards de Métricas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg p-5 text-white">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-clipboard-list text-white text-xl"></i>
                </div>
                <i class="fas fa-chart-line text-white/50 text-2xl"></i>
            </div>
            <p class="text-white/80 text-sm">Total de Ordens</p>
            <p class="text-3xl font-bold mt-1" id="totalOrders">0</p>
            <div class="mt-3 pt-2 border-t border-white/20">
                <span class="text-xs text-white/70">No período selecionado</span>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-lg p-5 text-white">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-white text-xl"></i>
                </div>
                <i class="fas fa-chart-line text-white/50 text-2xl"></i>
            </div>
            <p class="text-white/80 text-sm">Faturamento Total</p>
            <p class="text-3xl font-bold mt-1" id="totalRevenue">R$ 0,00</p>
            <div class="mt-3 pt-2 border-t border-white/20">
                <span class="text-xs text-white/70">No período selecionado</span>
            </div>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-lg p-5 text-white">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-car text-white text-xl"></i>
                </div>
                <i class="fas fa-chart-line text-white/50 text-2xl"></i>
            </div>
            <p class="text-white/80 text-sm">Ticket Médio</p>
            <p class="text-3xl font-bold mt-1" id="averageTicket">R$ 0,00</p>
            <div class="mt-3 pt-2 border-t border-white/20">
                <span class="text-xs text-white/70">Por ordem</span>
            </div>
        </div>

        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl shadow-lg p-5 text-white">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-white text-xl"></i>
                </div>
                <i class="fas fa-chart-line text-white/50 text-2xl"></i>
            </div>
            <p class="text-white/80 text-sm">Clientes Atendidos</p>
            <p class="text-3xl font-bold mt-1" id="totalCustomers">0</p>
            <div class="mt-3 pt-2 border-t border-white/20">
                <span class="text-xs text-white/70">No período</span>
            </div>
        </div>
    </div>

    {{-- Gráficos --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        {{-- Gráfico de Faturamento --}}
        <div class="bg-white rounded-2xl shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">
                    <i class="fas fa-chart-line text-blue-500 mr-2"></i> Evolução do Faturamento
                </h3>
            </div>
            <div class="p-6">
                <canvas id="revenueChart" height="250"></canvas>
            </div>
        </div>

        {{-- Gráfico de Ordens --}}
        <div class="bg-white rounded-2xl shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">
                    <i class="fas fa-chart-bar text-green-500 mr-2"></i> Quantidade de Ordens
                </h3>
            </div>
            <div class="p-6">
                <canvas id="ordersChart" height="250"></canvas>
            </div>
        </div>
    </div>

    {{-- Tabelas de Análise --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        {{-- Top Produtos --}}
        <div class="bg-white rounded-2xl shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">
                    <i class="fas fa-trophy text-yellow-500 mr-2"></i> Top 10 Produtos Mais Vendidos
                </h3>
            </div>
            <div class="p-6">
                <div id="topProductsList" class="space-y-4">
                    <div class="text-center py-8 text-gray-400">
                        <i class="fas fa-spinner fa-spin text-2xl"></i>
                        <p>Carregando...</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Métodos de Pagamento --}}
        <div class="bg-white rounded-2xl shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">
                    <i class="fas fa-credit-card text-purple-500 mr-2"></i> Métodos de Pagamento
                </h3>
            </div>
            <div class="p-6">
                <canvas id="paymentChart" height="200"></canvas>
            </div>
        </div>
    </div>

    {{-- Resumo Detalhado --}}
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">
                <i class="fas fa-table-list text-blue-500 mr-2"></i> Detalhamento por Período
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ordens</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Faturamento</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ticket Médio</th>
                    </tr>
                </thead>
                <tbody id="detailedTable" class="divide-y divide-gray-100">
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-400">
                            <i class="fas fa-spinner fa-spin text-2xl"></i>
                            <p>Carregando dados...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Data Personalizada --}}
<div id="customDateModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-xl bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Selecionar Período</h3>
            <button onclick="closeCustomDateModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data inicial</label>
                <input type="date" id="startDate" class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data final</label>
                <input type="date" id="endDate" class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div class="flex justify-end gap-3 pt-3">
                <button onclick="closeCustomDateModal()" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancelar</button>
                <button onclick="applyCustomDate()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Aplicar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let revenueChart, ordersChart, paymentChart;

    function setPeriod(period) {
        document.querySelectorAll('.period-btn').forEach(btn => {
            btn.classList.remove('bg-blue-100', 'text-blue-600');
            btn.classList.add('bg-gray-100', 'text-gray-600');
        });
        event.target.classList.remove('bg-gray-100', 'text-gray-600');
        event.target.classList.add('bg-blue-100', 'text-blue-600');
        
        fetch(`/manager/reports/data?period=${period}`)
            .then(response => response.json())
            .then(data => updateDashboard(data));
    }

    function openCustomDateModal() {
        document.getElementById('customDateModal').classList.remove('hidden');
    }

    function closeCustomDateModal() {
        document.getElementById('customDateModal').classList.add('hidden');
    }

    function applyCustomDate() {
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
        
        if (!startDate || !endDate) {
            alert('Selecione as datas inicial e final');
            return;
        }
        
        closeCustomDateModal();
        
        fetch(`/manager/reports/data?start=${startDate}&end=${endDate}`)
            .then(response => response.json())
            .then(data => updateDashboard(data));
        
        document.getElementById('period-label').innerText = `${startDate} até ${endDate}`;
    }

    function updateDashboard(data) {
        // Atualizar cards
        document.getElementById('totalOrders').innerText = data.totalOrders.toLocaleString();
        document.getElementById('totalRevenue').innerText = 'R$ ' + data.totalRevenue.toFixed(2).replace('.', ',');
        document.getElementById('averageTicket').innerText = 'R$ ' + data.averageTicket.toFixed(2).replace('.', ',');
        document.getElementById('totalCustomers').innerText = data.totalCustomers.toLocaleString();

        // Gráfico de Faturamento
        if (revenueChart) revenueChart.destroy();
        revenueChart = new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: {
                labels: data.revenueLabels,
                datasets: [{
                    label: 'Faturamento (R$)',
                    data: data.revenueData,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.3,
                    fill: true
                }]
            },
            options: { responsive: true, maintainAspectRatio: true }
        });

        // Gráfico de Ordens
        if (ordersChart) ordersChart.destroy();
        ordersChart = new Chart(document.getElementById('ordersChart'), {
            type: 'bar',
            data: {
                labels: data.ordersLabels,
                datasets: [{
                    label: 'Ordens',
                    data: data.ordersData,
                    backgroundColor: '#10b981',
                    borderRadius: 8
                }]
            },
            options: { responsive: true, maintainAspectRatio: true }
        });

        // Gráfico de Pagamentos
        if (paymentChart) paymentChart.destroy();
        paymentChart = new Chart(document.getElementById('paymentChart'), {
            type: 'doughnut',
            data: {
                labels: data.paymentLabels,
                datasets: [{
                    data: data.paymentData,
                    backgroundColor: ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b']
                }]
            },
            options: { responsive: true, maintainAspectRatio: true }
        });

        // Top Produtos
        const topProductsHtml = data.topProducts.map((p, i) => `
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="font-medium">${i+1}. ${p.name}</span>
                    <span class="text-gray-500">${p.total} vendas</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: ${p.percent}%"></div>
                </div>
            </div>
        `).join('');
        document.getElementById('topProductsList').innerHTML = topProductsHtml || '<div class="text-center py-8 text-gray-400">Nenhum dado disponível</div>';

        // Tabela detalhada
        const tableHtml = data.dailyData.map(day => `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-3 text-sm">${day.date}</td>
                <td class="px-6 py-3 text-right text-sm font-semibold">${day.orders}</td>
                <td class="px-6 py-3 text-right text-sm font-semibold text-green-600">R$ ${day.revenue}</td>
                <td class="px-6 py-3 text-right text-sm">R$ ${day.avgTicket}</td>
            </tr>
        `).join('');
        document.getElementById('detailedTable').innerHTML = tableHtml || '<tr><td colspan="4" class="px-6 py-8 text-center text-gray-400">Nenhum dado disponível</td></tr>';
    }

    // Carregar dados iniciais
    setPeriod('month');
</script>
@endsection