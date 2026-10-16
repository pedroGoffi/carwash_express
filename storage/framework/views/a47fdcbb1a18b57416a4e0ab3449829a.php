<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('header', 'Dashboard'); ?>
<?php $__env->startSection('subheader', 'Visão rápida da operação e do resultado'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $statusMeta = [
        'waiting' => ['label' => 'Em espera', 'class' => 'bg-amber-50 text-amber-700'],
        'queued' => ['label' => 'Na fila', 'class' => 'bg-blue-50 text-blue-700'],
        'finished' => ['label' => 'Aguardando entrega', 'class' => 'bg-emerald-50 text-emerald-700'],
        'delivered' => ['label' => 'Entregue', 'class' => 'bg-slate-100 text-slate-700'],
        'cancelled' => ['label' => 'Cancelada', 'class' => 'bg-rose-50 text-rose-700'],
    ];
?>

<div class="space-y-6" x-data="managerDashboard()" x-init="scheduleAnalytics()">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-950">Olá, <?php echo e(auth()->user()->name); ?></h2>
            <p class="text-sm text-slate-500 mt-1">Os dados operacionais aparecem primeiro; análises financeiras carregam em seguida sem travar a tela.</p>
        </div>
        <div class="flex gap-2">
            <a href="<?php echo e(route('operator.orders.index')); ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                <i class="fas fa-list"></i> Todas as ordens
            </a>
            <a href="<?php echo e(route('operator.orders.create')); ?>" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 shadow-sm">
                <i class="fas fa-plus"></i> Nova ordem
            </a>
        </div>
    </div>

    <div class="grid grid-cols-2 xl:grid-cols-5 gap-3 sm:gap-4">
        <div class="rounded-2xl bg-white border border-slate-200 p-4 sm:p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Ordens hoje</p>
            <p class="mt-2 text-3xl font-bold text-slate-950"><?php echo e($totalOrdersToday); ?></p>
            <p class="mt-1 text-xs text-slate-500"><?php echo e($totalOrdersMonth); ?> no mês</p>
        </div>
        <div class="rounded-2xl bg-white border border-slate-200 p-4 sm:p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Faturamento hoje</p>
            <p class="mt-2 text-2xl sm:text-3xl font-bold text-slate-950">R$ <?php echo e(number_format($totalRevenueToday, 2, ',', '.')); ?></p>
            <p class="mt-1 text-xs text-slate-500">Ticket médio R$ <?php echo e(number_format($averageTicketToday, 2, ',', '.')); ?></p>
        </div>
        <div class="rounded-2xl bg-amber-50 border border-amber-100 p-4 sm:p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-amber-600">Em espera</p>
            <p class="mt-2 text-3xl font-bold text-amber-900"><?php echo e($waitingCount); ?></p>
            <p class="mt-1 text-xs text-amber-700">Veículos deixados para depois</p>
        </div>
        <div class="rounded-2xl bg-blue-50 border border-blue-100 p-4 sm:p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-blue-600">Na fila</p>
            <p class="mt-2 text-3xl font-bold text-blue-900"><?php echo e($queueCount); ?></p>
            <p class="mt-1 text-xs text-blue-700">Aguardando lavagem</p>
        </div>
        <div class="rounded-2xl bg-emerald-50 border border-emerald-100 p-4 sm:p-5 col-span-2 xl:col-span-1">
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">Prontos</p>
            <p class="mt-2 text-3xl font-bold text-emerald-900"><?php echo e($readyCount); ?></p>
            <p class="mt-1 text-xs text-emerald-700">Aguardando entrega</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <section class="xl:col-span-2 rounded-2xl bg-white border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900">Últimas ordens</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Carregadas junto com a tela inicial</p>
                </div>
                <a href="<?php echo e(route('operator.orders.index')); ?>" class="text-sm font-semibold text-blue-600 hover:text-blue-700">Ver todas</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px]">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="text-left px-5 py-3">OS</th>
                            <th class="text-left px-5 py-3">Cliente / veículo</th>
                            <th class="text-left px-5 py-3">Status</th>
                            <th class="text-right px-5 py-3">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    <?php $__empty_1 = true; $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php ($meta = $statusMeta[$order['status']] ?? ['label' => ucfirst($order['status']), 'class' => 'bg-slate-100 text-slate-700']); ?>
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-5 py-4 font-mono text-sm font-bold text-blue-700">#<?php echo e($order['code']); ?></td>
                            <td class="px-5 py-4">
                                <p class="font-semibold text-sm text-slate-800"><?php echo e($order['customer_name'] ?: 'Cliente não informado'); ?></p>
                                <p class="text-xs text-slate-500 mt-0.5"><?php echo e($order['license_plate'] ?: 'Sem placa'); ?></p>
                            </td>
                            <td class="px-5 py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold <?php echo e($meta['class']); ?>"><?php echo e($meta['label']); ?></span></td>
                            <td class="px-5 py-4 text-right font-bold text-slate-900">R$ <?php echo e(number_format($order['total'], 2, ',', '.')); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4" class="px-5 py-12 text-center text-sm text-slate-400">Nenhuma ordem registrada.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-2xl bg-white border border-slate-200 text-slate-900 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[.16em] text-slate-500">Resultado do mês</p>
                    <h3 class="text-lg font-bold mt-1">Financeiro</h3>
                </div>
                <span x-show="loading" class="text-xs text-slate-400">Carregando…</span>
            </div>

            <template x-if="analyticsError">
                <div class="mt-6 rounded-xl bg-amber-50 border border-amber-200 p-4 text-sm text-amber-900">
                    <p class="font-semibold">Análise temporariamente indisponível</p>
                    <p class="text-xs text-amber-700 mt-1">A operação continua funcionando. Você pode tentar novamente sem sair da página.</p>
                    <button @click="loadAnalytics()" class="mt-3 rounded-lg bg-blue-50 text-blue-700 px-3 py-2 text-xs font-bold hover:bg-blue-100">Tentar novamente</button>
                </div>
            </template>

            <div x-show="!analyticsError" class="mt-5 space-y-4">
                <div class="rounded-xl bg-slate-50 p-4">
                    <p class="text-xs text-slate-400">Faturamento</p>
                    <p class="text-2xl font-bold mt-1" x-text="money(analytics.monthRevenue)">—</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-slate-50 p-3"><p class="text-[11px] text-slate-400">Custos</p><p class="font-bold mt-1" x-text="money(analytics.expenses)">—</p></div>
                    <div class="rounded-xl bg-slate-50 p-3"><p class="text-[11px] text-slate-400">Resultado</p><p class="font-bold mt-1" :class="analytics.profit >= 0 ? 'text-emerald-600' : 'text-rose-600'" x-text="money(analytics.profit)">—</p></div>
                </div>
                <a href="<?php echo e(route('manager.payroll.index')); ?>" class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold hover:bg-slate-50">
                    Folha & custos <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </section>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <section class="rounded-2xl bg-white border border-slate-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-5">
                <div><h3 class="font-bold text-slate-900">Últimos 6 meses</h3><p class="text-xs text-slate-500 mt-0.5">Receita x custos</p></div>
                <span class="text-[11px] text-slate-400">cache de 60s</span>
            </div>
            <div x-show="loading" class="space-y-3 animate-pulse">
                <div class="h-10 bg-slate-100 rounded-lg"></div><div class="h-10 bg-slate-100 rounded-lg"></div><div class="h-10 bg-slate-100 rounded-lg"></div>
            </div>
            <div x-show="!loading && !analyticsError" class="space-y-4">
                <template x-for="row in analytics.months" :key="row.label">
                    <div>
                        <div class="flex justify-between gap-3 text-xs mb-1.5"><span class="font-semibold text-slate-600" x-text="row.label"></span><span class="text-slate-500" x-text="money(row.revenue)"></span></div>
                        <div class="h-2.5 bg-slate-100 rounded-full overflow-hidden"><div class="h-full rounded-full bg-blue-600" :style="`width:${barWidth(row.revenue)}%`"></div></div>
                    </div>
                </template>
            </div>
        </section>

        <section class="rounded-2xl bg-white border border-slate-200 shadow-sm p-5">
            <div class="mb-5"><h3 class="font-bold text-slate-900">Serviços mais vendidos</h3><p class="text-xs text-slate-500 mt-0.5">Quantidade acumulada</p></div>
            <div x-show="loading" class="space-y-3 animate-pulse"><div class="h-12 bg-slate-100 rounded-lg"></div><div class="h-12 bg-slate-100 rounded-lg"></div><div class="h-12 bg-slate-100 rounded-lg"></div></div>
            <div x-show="!loading && !analyticsError" class="space-y-3">
                <template x-for="(item,index) in analytics.topProducts" :key="item.name">
                    <div class="flex items-center gap-3 rounded-xl border border-slate-100 p-3">
                        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600" x-text="index+1"></div>
                        <div class="min-w-0 flex-1"><p class="font-semibold text-sm text-slate-800 truncate" x-text="item.name"></p></div>
                        <span class="text-sm font-bold text-slate-900" x-text="item.total"></span>
                    </div>
                </template>
                <p x-show="analytics.topProducts.length === 0" class="py-10 text-center text-sm text-slate-400">Sem dados no período.</p>
            </div>
        </section>
    </div>
</div>

<script>
function managerDashboard() {
    return {
        loading: true,
        analyticsError: false,
        analytics: { monthRevenue: 0, expenses: 0, profit: 0, months: [], topProducts: [] },
        scheduleAnalytics() {
            if ('requestIdleCallback' in window) requestIdleCallback(() => this.loadAnalytics(), {timeout: 1700});
            else setTimeout(() => this.loadAnalytics(), 150);
        },
        async loadAnalytics() {
            this.loading = true;
            this.analyticsError = false;
            try {
                const controller = new AbortController();
                const timer = setTimeout(() => controller.abort(), 7000);
                const response = await fetch(<?php echo json_encode(route('manager.dashboard.analytics'), 15, 512) ?>, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    signal: controller.signal,
                });
                clearTimeout(timer);
                if (!response.ok) throw new Error('analytics unavailable');
                this.analytics = await response.json();
            } catch (error) {
                this.analyticsError = true;
            } finally {
                this.loading = false;
            }
        },
        money(value) {
            return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(value || 0));
        },
        barWidth(value) {
            const values = (this.analytics.months || []).map(i => Number(i.revenue || 0));
            const max = Math.max(...values, 1);
            return Math.max(2, (Number(value || 0) / max) * 100);
        },
    }
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/pedro/Desktop/MerchOnline/carwash/carwash_express/resources/views/manager/dashboard.blade.php ENDPATH**/ ?>