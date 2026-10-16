<?php $__env->startSection('title', 'Editar Produto'); ?>
<?php $__env->startSection('header', 'Editar Produto'); ?>
<?php $__env->startSection('subheader', 'Altere as informações do produto'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6">
        
        <form action="<?php echo e(route('manager.products.update', $product)); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Produto *</label>
                    <input type="text" name="name" value="<?php echo e(old('name', $product->name)); ?>" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                    <select name="type" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="wash" <?php echo e($product->type == 'wash' ? 'selected' : ''); ?>>Lavagem</option>
                        <option value="drink" <?php echo e($product->type == 'drink' ? 'selected' : ''); ?>>Bebida</option>
                        <option value="snack" <?php echo e($product->type == 'snack' ? 'selected' : ''); ?>>Comida</option>
                    </select>
                </div>

                
                <div class="bg-gray-50 rounded-lg p-4">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        <i class="fas fa-table mr-1"></i> Tabela de Preços
                    </label>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2 px-3 text-sm font-medium">Tipo do Veículo</th>
                                    <th class="text-left py-2 px-3 text-sm font-medium">Preço (R$)</th>
                                    <th class="text-left py-2 px-3 text-sm font-medium w-20"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b">
                                    <td class="py-2 px-3">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-car-side text-blue-500"></i>
                                            <span class="text-sm">Carro Pequeno</span>
                                        </div>
                                    </td>
                                    <td class="py-2 px-3">
                                        <input type="number" name="prices[normal]" step="0.01" required
                                               value="<?php echo e(old('prices.normal', $prices['normal']->price ?? '')); ?>"
                                               class="w-32 border rounded-lg px-2 py-1 text-sm">
                                    </td>
                                    <td class="py-2 px-3 text-xs text-gray-400">Base</td>
                                </tr>
                                <tr class="border-b">
                                    <td class="py-2 px-3">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-caravan text-green-500"></i>
                                            <span class="text-sm">SUV / Picape</span>
                                        </div>
                                    </td>
                                    <td class="py-2 px-3">
                                        <input type="number" name="prices[suv]" step="0.01"
                                               value="<?php echo e(old('prices.suv', $prices['suv']->price ?? '')); ?>"
                                               class="w-32 border rounded-lg px-2 py-1 text-sm">
                                    </td>
                                    <td class="py-2 px-3 text-xs text-gray-400">Opcional</td>
                                </tr>
                                <tr class="border-b">
                                    <td class="py-2 px-3">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-truck-pickup text-orange-500"></i>
                                            <span class="text-sm">Caminhonete Grande</span>
                                        </div>
                                    </td>
                                    <td class="py-2 px-3">
                                        <input type="number" name="prices[pickup]" step="0.01"
                                               value="<?php echo e(old('prices.pickup', $prices['pickup']->price ?? '')); ?>"
                                               class="w-32 border rounded-lg px-2 py-1 text-sm">
                                    </td>
                                    <td class="py-2 px-3 text-xs text-gray-400">Opcional</td>
                                </tr>                                
                            </tbody>
                        </table>
                    </div>
                    <p class="text-xs text-gray-400 mt-3">
                        <i class="fas fa-info-circle"></i> Se não informar preço para um tipo específico, será usado o preço do "Carro Pequeno"
                    </p>
                </div>

                <div>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="active" value="1" <?php echo e($product->active ? 'checked' : ''); ?>>
                        <span class="text-sm text-gray-700">Produto ativo</span>
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <a href="<?php echo e(route('manager.products.index')); ?>" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancelar</a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Atualizar</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/pedro/Desktop/MerchOnline/carwash/carwash_express/resources/views/manager/products/edit.blade.php ENDPATH**/ ?>