<?php $__env->startSection('title', 'Relatórios'); ?>
<?php $__env->startSection('header', 'Relatórios Gerenciais'); ?>
<?php $__env->startSection('subheader', 'Análise completa da operação'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    
    
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
                <button onclick="setPeriod('today', this)" class="period-btn px-4 py-2 text-sm rounded-lg bg-gray-100 text-gray-600 hover:bg-blue-100 hover:text-blue-600 transition">Hoje</button>
                <button onclick="setPeriod('yesterday', this)" class="period-btn px-4 py-2 text-sm rounded-lg bg-gray-100 text-gray-600 hover:bg-blue-100 hover:text-blue-600 transition">Ontem</button>
                <button onclick="setPeriod('week', this)" class="period-btn px-4 py-2 text-sm rounded-lg bg-gray-100 text-gray-600 hover:bg-blue-100 hover:text-blue-600 transition">Esta semana</button>
                <button onclick="setPeriod('month', this)" class="period-btn px-4 py-2 text-sm rounded-lg bg-gray-100 text-gray-600 hover:bg-blue-100 hover:text-blue-600 transition">Este mês</button>
                <button onclick="setPeriod('last6months', this)" class="period-btn px-4 py-2 text-sm rounded-lg bg-gray-100 text-gray-600 hover:bg-blue-100 hover:text-blue-600 transition">Últimos 6 meses</button>
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
                <i class="fas fa-sync-alt mr-1"></i> Última atualização: <?php echo e(now()->format('d/m/Y H:i')); ?>

            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 text-slate-900">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-clipboard-list text-blue-600 text-xl"></i>
                </div>
                <i class="fas fa-chart-line text-blue-400 text-2xl"></i>
            </div>
            <p class="text-slate-500 text-sm">Total de Ordens</p>
            <p class="text-3xl font-bold mt-1" id="totalOrders">0</p>
            <div class="mt-3 pt-2 border-t border-slate-100">
                <span class="text-xs text-slate-400">No período selecionado</span>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 text-slate-900">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-blue-600 text-xl"></i>
                </div>
                <i class="fas fa-chart-line text-blue-400 text-2xl"></i>
            </div>
            <p class="text-slate-500 text-sm">Faturamento Total</p>
            <p class="text-3xl font-bold mt-1" id="totalRevenue">R$ 0,00</p>
            <div class="mt-3 pt-2 border-t border-slate-100">
                <span class="text-xs text-slate-400">No período selecionado</span>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 text-slate-900">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-car text-blue-600 text-xl"></i>
                </div>
                <i class="fas fa-chart-line text-blue-400 text-2xl"></i>
            </div>
            <p class="text-slate-500 text-sm">Ticket Médio</p>
            <p class="text-3xl font-bold mt-1" id="averageTicket">R$ 0,00</p>
            <div class="mt-3 pt-2 border-t border-slate-100">
                <span class="text-xs text-slate-400">Por ordem</span>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 text-slate-900">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
                <i class="fas fa-chart-line text-blue-400 text-2xl"></i>
            </div>
            <p class="text-slate-500 text-sm">Clientes Atendidos</p>
            <p class="text-3xl font-bold mt-1" id="totalCustomers">0</p>
            <div class="mt-3 pt-2 border-t border-slate-100">
                <span class="text-xs text-slate-400">No período</span>
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        
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

    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        
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

<script>
let revenueChart, ordersChart, paymentChart, controller;
const money = n => Number(n || 0).toLocaleString('pt-BR',{style:'currency',currency:'BRL'});
const escapeText = str => String(str ?? '').replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]));
let chartsReady;
function ensureCharts() {
    if (window.Chart) return Promise.resolve(true);
    if (!chartsReady) chartsReady = new Promise(resolve => {
        const script = document.createElement('script');
        script.src='https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js';
        script.async=true;
        script.onload=() => resolve(!!window.Chart);
        script.onerror=() => resolve(false);
        document.head.appendChild(script);
    });
    return chartsReady;
}
async function fetchReport(params) {
    if (controller) controller.abort();
    controller = new AbortController();
    try {
        const response = await fetch(<?php echo json_encode(route('manager.reports.data'), 15, 512) ?> + '?' + params.toString(), {
            headers:{'Accept':'application/json'},signal: controller.signal
        });
        if (!response.ok) throw new Error('Falha ao consultar relatórios');
        const data = await response.json();
        updateDashboard(data);
        // A biblioteca de gráficos carrega só depois que os indicadores aparecem.
        if (await ensureCharts()) drawCharts(data);
    } catch (e) {
        if (e.name === 'AbortError') return;
        document.getElementById('topProductsList').textContent='Não foi possível carregar o relatório. Selecione outro período para tentar novamente.';
        document.getElementById('detailedTable').innerHTML='<tr><td colspan="4" class="px-6 py-8 text-slate-500">Relatório indisponível.</td></tr>';
    }
}
function setPeriod(period, button=null) {
    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.classList.toggle('bg-blue-100', btn === button);
        btn.classList.toggle('text-blue-600', btn === button);
        btn.classList.toggle('bg-gray-100', btn !== button);
        btn.classList.toggle('text-gray-600', btn !== button);
    });
    const labels={today:'Hoje',yesterday:'Ontem',week:'Esta semana',month:'Este mês',last6months:'Últimos 6 meses'};
    document.getElementById('period-label').textContent=labels[period] ?? 'Período';
    fetchReport(new URLSearchParams({period}));
}
function openCustomDateModal() { document.getElementById('customDateModal').classList.remove('hidden'); }
function closeCustomDateModal() { document.getElementById('customDateModal').classList.add('hidden'); }
function applyCustomDate() {
    const start=document.getElementById('startDate').value;
    const end=document.getElementById('endDate').value;
    if (!start || !end || start>end) { alert('Selecione um período válido.'); return; }
    closeCustomDateModal();
    document.getElementById('period-label').textContent=`${start} até ${end}`;
    fetchReport(new URLSearchParams({start,end}));
}
function updateDashboard(data) {
    document.getElementById('totalOrders').textContent=Number(data.totalOrders || 0).toLocaleString('pt-BR');
    document.getElementById('totalRevenue').textContent=money(data.totalRevenue);
    document.getElementById('averageTicket').textContent=money(data.averageTicket);
    document.getElementById('totalCustomers').textContent=Number(data.totalCustomers || 0).toLocaleString('pt-BR');
    const products=(data.topProducts || []).map((x,i)=>`<div class="mb-3"><div class="flex justify-between text-sm"><span>${i+1}. ${escapeText(x.name)}</span><span class="text-slate-500">${Number(x.total)||0} vendas</span></div><div class="h-2 rounded-full bg-slate-100 mt-2"><div class="h-2 rounded-full bg-blue-500" style="width:${Math.min(100,Math.max(0, Number(x.percent)||0))}%"></div></div></div>`).join('');
    document.getElementById('topProductsList').innerHTML=products || '<p class="text-slate-500">Sem produtos no período.</p>';
    const rows=(data.dailyData || []).map(x=>`<tr><td class="px-6 py-3">${escapeText(x.date)}</td><td class="px-6 py-3 text-right">${Number(x.orders)||0}</td><td class="px-6 py-3 text-right">R$ ${escapeText(x.revenue)}</td><td class="px-6 py-3 text-right">R$ ${escapeText(x.avgTicket)}</td></tr>`).join('');
    document.getElementById('detailedTable').innerHTML=rows || '<tr><td colspan="4" class="px-6 py-8 text-center text-slate-500">Sem dados no período.</td></tr>';
}
function drawCharts(data) {
    [revenueChart,ordersChart,paymentChart].forEach(c=>c?.destroy());
    revenueChart=new Chart(document.getElementById('revenueChart'),{type:'line',data:{labels:data.revenueLabels||[],datasets:[{label:'Receita',data:data.revenueData||[],borderColor:'#2563eb',tension:.3,fill:false}]},options:{responsive:true,maintainAspectRatio:true}});
    ordersChart=new Chart(document.getElementById('ordersChart'),{type:'bar',data:{labels:data.ordersLabels||[],datasets:[{label:'Pagamentos registrados',data:data.ordersData||[],backgroundColor:'#2563eb',borderRadius:5}]},options:{responsive:true,maintainAspectRatio:true}});
    paymentChart=new Chart(document.getElementById('paymentChart'),{type:'doughnut',data:{labels:data.paymentLabels||[],datasets:[{data:data.paymentData||[],backgroundColor:['#2563eb','#60a5fa','#93c5fd','#bfdbfe']}]},options:{responsive:true,maintainAspectRatio:true}});
}
// Primeiro pinta a página, depois busca dados em paralelo à navegação.
if ('requestIdleCallback' in window) requestIdleCallback(()=>setPeriod('month'),{timeout:1200});
else setTimeout(()=>setPeriod('month'),50);
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/pedro/Desktop/MerchOnline/carwash/carwash_express/resources/views/manager/reports/index.blade.php ENDPATH**/ ?>