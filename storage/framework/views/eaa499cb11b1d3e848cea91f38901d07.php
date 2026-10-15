<?php $__env->startSection('title', 'Consumo de Produtos'); ?>
<?php $__env->startSection('header', 'Consumo de Funcionários'); ?>
<?php $__env->startSection('subheader', 'Registre refrigerantes, lanches e outros itens consumidos'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 text-slate-900">
            <div class="flex items-center gap-3 mb-3">
                <i class="fas fa-coffee text-2xl text-slate-500"></i>
                <p class="text-slate-500 text-sm">Total Consumido</p>
            </div>
            <p class="text-3xl font-bold">R$ <?php echo e(number_format($consumptions->sum('amount'), 2, ',', '.')); ?></p>
        </div>
        
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 text-slate-900">
            <div class="flex items-center gap-3 mb-3">
                <i class="fas fa-bottle-water text-2xl text-slate-500"></i>
                <p class="text-slate-500 text-sm">Itens Consumidos</p>
            </div>
            <p class="text-3xl font-bold"><?php echo e($consumptions->sum('quantity')); ?> unidades</p>
        </div>
        
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 text-slate-900">
            <div class="flex items-center gap-3 mb-3">
                <i class="fas fa-users text-2xl text-slate-500"></i>
                <p class="text-slate-500 text-sm">Funcionários</p>
            </div>
            <p class="text-3xl font-bold"><?php echo e($workers->count()); ?></p>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-plus-circle text-blue-500"></i> Registrar Consumo
        </h3>
        <form action="<?php echo e(route('manager.consumptions.store')); ?>" method="POST" class="grid grid-cols-1 md:grid-cols-5 gap-4">
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
                <label class="block text-sm font-medium text-gray-700 mb-1">Produto</label>
                <select name="product_id" id="product_id" required class="w-full border rounded-lg px-3 py-2">
                    <option value="">Selecione</option>                    
                    <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($product->id); ?>" data-price="<?php echo e($products[0]->prices[0]->price); ?>">
                            <?php echo e($product->name); ?> - R$ <?php echo e(number_format($products[0]->prices[0]->price, 2, ',', '.')); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade</label>
                <input type="number" name="quantity" id="quantity" min="1" value="1" required class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data</label>
                <input type="date" name="date" value="<?php echo e(now()->format('Y-m-d')); ?>" required class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Motivo</label>
                <input type="text" name="reason" class="w-full border rounded-lg px-3 py-2" placeholder="Opcional">
            </div>
            <div class="md:col-span-5 flex justify-end">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i> Registrar Consumo
                </button>
            </div>
        </form>
    </div>

    
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">
                <i class="fas fa-list text-blue-500 mr-2"></i> Histórico de Consumos
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Funcionário</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produto</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Qtd</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Valor Unit.</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Motivo</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $consumptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $consumption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="px-6 py-3 text-sm"><?php echo e($consumption->date->format('d/m/Y')); ?></td>
                        <td class="px-6 py-3 text-sm"><?php echo e($consumption->user->name); ?></td>
                        <td class="px-6 py-3 text-sm"><?php echo e($consumption->product->name); ?></td>
                        <td class="px-6 py-3 text-center text-sm font-semibold"><?php echo e($consumption->quantity); ?>x</td>
                        <td class="px-6 py-3 text-right text-sm">R$ <?php echo e(number_format($consumption->unit_price, 2, ',', '.')); ?></td>
                        <td class="px-6 py-3 text-right text-sm font-semibold text-red-600">- R$ <?php echo e(number_format($consumption->amount, 2, ',', '.')); ?></td>
                        <td class="px-6 py-3 text-sm text-gray-500"><?php echo e($consumption->reason ?? '-'); ?></td>
                        <td class="px-6 py-3 text-center">
                            <?php if(auth()->user()->isManager()): ?>
                                <form action="<?php echo e(route('manager.consumptions.destroy', $consumption)); ?>" method="POST" class="inline" onsubmit="return confirm('Remover este consumo?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="text-red-500 hover:text-red-700">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>                            
                            <?php else: ?>
                                <h1>-</h1>
                            <?php endif; ?>

                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-400">
                            <i class="fas fa-inbox text-2xl mb-2"></i>
                            <p>Nenhum consumo registrado</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot class="bg-gray-50 font-bold">
                    <tr>
                        <td colspan="5" class="px-6 py-3 text-right">TOTAL CONSUMIDO:</td>
                        <td class="px-6 py-3 text-right text-red-600">- R$ <?php echo e(number_format($consumptions->sum('amount'), 2, ',', '.')); ?></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script>
    // Mostrar preço do produto ao selecionar
    document.getElementById('product_id').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const price = selected.dataset.price;
        if (price) {
            console.log('Preço selecionado: R$', price);
        }
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/pedro/Desktop/MerchOnline/carwash/carwash_express/resources/views/manager/consumptions/index.blade.php ENDPATH**/ ?>