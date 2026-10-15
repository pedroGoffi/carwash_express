<?php $__env->startSection('title', 'Controle de Vales'); ?>
<?php $__env->startSection('header', 'Vales dos Funcionários'); ?>
<?php $__env->startSection('subheader', 'Registre e acompanhe os adiantamentos'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    
    
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-hand-holding-usd text-blue-500"></i> Registrar Novo Vale
        </h3>
        <form action="<?php echo e(route('manager.advances.store')); ?>" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Funcionário</label>
                <select name="user_id" required class="w-full border rounded-lg px-3 py-2">
                    <option value="">Selecione</option>
                    <?php $__currentLoopData = $workers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $worker): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($worker->id); ?>"><?php echo e($worker->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Valor (R$)</label>
                <input type="number" name="amount" step="0.01" required class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data</label>
                <input type="date" name="date" value="<?php echo e(now()->format('Y-m-d')); ?>" required class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Motivo</label>
                <input type="text" name="reason" class="w-full border rounded-lg px-3 py-2" placeholder="Opcional">
            </div>
            <div class="md:col-span-4 flex justify-end">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i> Registrar Vale
                </button>
            </div>
        </form>
    </div>

    
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">
                <i class="fas fa-list text-blue-500 mr-2"></i> Histórico de Vales
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Funcionário</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Valor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Motivo</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $advances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $advance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="px-6 py-3 text-sm"><?php echo e($advance->date->format('d/m/Y')); ?></td>
                        <td class="px-6 py-3 text-sm"><?php echo e($advance->user->name); ?></td>
                        <td class="px-6 py-3 text-right text-sm font-semibold text-red-600">- R$ <?php echo e(number_format($advance->amount, 2, ',', '.')); ?></td>
                        <td class="px-6 py-3 text-sm text-gray-500"><?php echo e($advance->reason ?? '-'); ?></td>
                        <td class="px-6 py-3 text-right">
                            <form action="<?php echo e(route('manager.advances.destroy', $advance)); ?>" method="POST" class="inline" onsubmit="return confirm('Remover este vale?')">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="text-red-500 hover:text-red-700">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                            <i class="fas fa-inbox text-2xl mb-2"></i>
                            <p>Nenhum vale registrado</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/pedro/Desktop/MerchOnline/carwash/carwash_express/resources/views/manager/advances/index.blade.php ENDPATH**/ ?>