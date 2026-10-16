<?php $__env->startSection('title', 'Administração'); ?>
<?php $__env->startSection('header', 'Administração'); ?>
<?php $__env->startSection('subheader', 'Visão geral do CarWash Express'); ?>
<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <?php $__currentLoopData = [
            ['label' => 'Empresas', 'value' => number_format($totalCompanies, 0, ',', '.')],
            ['label' => 'Usuários', 'value' => number_format($totalUsers, 0, ',', '.')],
            ['label' => 'Ordens', 'value' => number_format($totalOrders, 0, ',', '.')],
            ['label' => 'Receita registrada', 'value' => 'R$ '.number_format($totalRevenue, 2, ',', '.')],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-medium text-slate-500"><?php echo e($metric['label']); ?></p>
                <p class="mt-2 text-2xl font-bold text-slate-900"><?php echo e($metric['value']); ?></p>
            </section>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-100 px-5 py-4 flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-slate-900">Ordens recentes</h2>
            <a class="text-blue-600 text-sm font-semibold" href="<?php echo e(route('operator.orders.index')); ?>">Ver todas</a>
        </div>
        <div class="overflow-x-auto"><table class="w-full min-w-[620px] text-sm">
            <thead class="bg-slate-50 text-slate-500"><tr><th class="text-left p-4">OS</th><th class="text-left p-4">Empresa</th><th class="text-left p-4">Cliente</th><th class="text-right p-4">Total</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
            <?php $__empty_1 = true; $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr><td class="p-4"><?php echo e($order->code ?? '#'.$order->id); ?></td><td class="p-4"><?php echo e($order->company?->legal_name ?? '—'); ?></td><td class="p-4"><?php echo e($order->customer_name ?? '—'); ?></td><td class="p-4 text-right">R$ <?php echo e(number_format((float) $order->total, 2, ',', '.')); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td class="p-8 text-center text-slate-500" colspan="4">Nenhuma ordem registrada.</td></tr>
            <?php endif; ?>
            </tbody>
        </table></div>
    </section>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/pedro/Desktop/MerchOnline/carwash/carwash_express/resources/views/admin/dashboard.blade.php ENDPATH**/ ?>