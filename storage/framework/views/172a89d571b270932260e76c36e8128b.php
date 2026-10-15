<?php $__env->startSection('title', 'Finalizar Serviço'); ?>
<?php $__env->startSection('header', 'Devolução de Veículo'); ?>
<?php $__env->startSection('subheader', 'Confirme os dados e finalize a ordem de serviço'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        
        
        <div class="bg-white border-b border-slate-100 px-6 py-5">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-blue-50 rounded-full flex items-center justify-center">
                    <i class="fas fa-key text-blue-600 text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-slate-900 text-xl font-bold">Devolução do Veículo</h2>
                    <p class="text-slate-500 text-sm">Confirme os dados e entregue as chaves ao cliente</p>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-6">
            
            
            <div class="bg-gray-50 rounded-xl p-5">
                <div class="flex items-center gap-2 mb-4">
                    <i class="fas fa-clipboard-list text-blue-500"></i>
                    <h3 class="font-semibold text-gray-700">Ordem de Serviço</h3>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-xs text-gray-400">Número</p>
                        <p class="font-bold text-lg text-blue-600"><?php echo e($order->code); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Data</p>
                        <p class="font-semibold"><?php echo e($order->created_at->format('d/m/Y')); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Horário</p>
                        <p class="font-semibold"><?php echo e($order->created_at->format('H:i')); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Status</p>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i> Finalizado
                        </span>
                    </div>
                </div>
            </div>

            
            <div class="bg-gray-50 rounded-xl p-5">
                <div class="flex items-center gap-2 mb-4">
                    <i class="fas fa-user-circle text-blue-500"></i>
                    <h3 class="font-semibold text-gray-700">Dados do Cliente</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php if($order->customer_type == 'pj'): ?>
                        <div>
                            <p class="text-xs text-gray-400">Razão Social</p>
                            <p class="font-medium"><?php echo e($order->customer_name); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">CNPJ</p>
                            <p class="font-medium"><?php echo e($order->customer_document); ?></p>
                        </div>
                        <?php if($order->contact_name): ?>
                        <div>
                            <p class="text-xs text-gray-400">Contato</p>
                            <p class="font-medium"><?php echo e($order->contact_name); ?></p>
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div>
                            <p class="text-xs text-gray-400">Nome</p>
                            <p class="font-medium"><?php echo e($order->customer_name ?? 'NÃO IDENTIFICADO'); ?></p>
                        </div>
                    <?php endif; ?>
                    <div>
                        <p class="text-xs text-gray-400">Telefone</p>
                        <p class="font-medium">-</p>
                    </div>
                </div>
            </div>

            
            <div class="bg-gray-50 rounded-xl p-5">
                <div class="flex items-center gap-2 mb-4">
                    <i class="fas fa-car text-blue-500"></i>
                    <h3 class="font-semibold text-gray-700">Dados do Veículo</h3>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-xs text-gray-400">Placa</p>
                        <p class="font-bold text-lg uppercase"><?php echo e($order->license_plate); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Tipo</p>
                        <p class="font-medium">
                            <?php if($order->vehicle_type == 'normal'): ?> Carro
                            <?php elseif($order->vehicle_type == 'suv'): ?> SUV / Picape
                            <?php else: ?> Caminhonete
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-xs text-gray-400">Serviços Realizados</p>
                        <div class="flex flex-wrap gap-1 mt-1">
                            <?php $__currentLoopData = $order->products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="inline-block px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                    <?php if($item->pivot->quantity > 1): ?><?php echo e($item->pivot->quantity); ?>× <?php endif; ?><?php echo e($item->name); ?>

                                </span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-5 border border-green-200">
                <div class="flex items-center gap-2 mb-4">
                    <i class="fas fa-dollar-sign text-green-600"></i>
                    <h3 class="font-semibold text-gray-700">Resumo Financeiro</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-xs text-gray-400">Total do Serviço</p>
                        <p class="text-2xl font-bold text-green-600">R$ <?php echo e(number_format($order->total, 2, ',', '.')); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Forma de Pagamento</p>
                        <div class="space-y-1 mt-1">
                            <?php $__currentLoopData = $order->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="flex items-center gap-2">
                                    <i class="fas <?php echo e($payment->method == 'cash' ? 'fa-money-bill-wave' : ($payment->method == 'pix' ? 'fa-qrcode' : 'fa-credit-card')); ?> text-gray-500 text-sm"></i>
                                    <span class="text-sm"><?php echo e($payment->method); ?>: R$ <?php echo e(number_format($payment->amount, 2, ',', '.')); ?></span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Status do Pagamento</p>
                        <?php
                            $totalPaid = $order->payments->sum('amount');
                            $isFullyPaid = $totalPaid >= $order->total;
                        ?>
                        <?php if($isFullyPaid): ?>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 mt-1">
                                <i class="fas fa-check-circle mr-1"></i> PAGO
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 mt-1">
                                <i class="fas fa-exclamation-circle mr-1"></i> PENDENTE (R$ <?php echo e(number_format($order->total - $totalPaid, 2, ',', '.')); ?>)
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php
                $totalPaid = (float) $order->payments->sum('amount');
                $remainingPayment = max(0, (float) $order->total - $totalPaid);
            ?>
            <?php if($remainingPayment > 0): ?>
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-5">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <p class="font-bold text-amber-900">Pagamento pendente</p>
                        <p class="text-sm text-amber-700 mt-1">Saldo: R$ <?php echo e(number_format($remainingPayment, 2, ',', '.')); ?></p>
                    </div>
                    <form method="POST" action="<?php echo e(route('operator.orders.payments.store', $order)); ?>" class="flex flex-col sm:flex-row gap-2">
                        <?php echo csrf_field(); ?>
                        <select name="method" required class="rounded-lg border-amber-200 bg-white">
                            <option value="">Forma</option>
                            <option value="cash">Dinheiro</option>
                            <option value="pix">Pix</option>
                            <option value="credit">Crédito</option>
                            <option value="debit">Débito</option>
                        </select>
                        <input type="number" name="amount" min="0.01" max="<?php echo e($remainingPayment); ?>" step="0.01" value="<?php echo e(number_format($remainingPayment, 2, '.', '')); ?>" required class="rounded-lg border-amber-200 w-36">
                        <button class="rounded-lg bg-amber-500 text-white px-4 py-2 font-semibold hover:bg-amber-600">Registrar</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            
            <div class="bg-gray-50 rounded-xl p-5">
                <div class="flex items-center gap-2 mb-4">
                    <i class="fas fa-clipboard-check text-blue-500"></i>
                    <h3 class="font-semibold text-gray-700">Checklist de Devolução</h3>
                </div>
                <div class="space-y-3">
                    <label class="flex items-center gap-3 p-2 bg-white rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" id="check_keys" class="w-5 h-5 text-green-600 rounded">
                        <span class="font-medium">Chaves do veículo devolvidas ao cliente</span>
                    </label>
                    <label class="flex items-center gap-3 p-2 bg-white rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" id="check_damage" class="w-5 h-5 text-green-600 rounded">
                        <span class="font-medium">Cliente verificou o veículo e aprovou</span>
                    </label>
                    <label class="flex items-center gap-3 p-2 bg-white rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" id="check_payment" class="w-5 h-5 text-green-600 rounded">
                        <span class="font-medium">Pagamento confirmado</span>
                    </label>
                    <label class="flex items-center gap-3 p-2 bg-white rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" id="check_document" class="w-5 h-5 text-green-600 rounded">
                        <span class="font-medium">Comprovante entregue ao cliente</span>
                    </label>
                </div>
            </div>

            
            <div class="flex justify-between gap-3 pt-4 border-t">
                <a href="<?php echo e(route('operator.orders.create')); ?>" 
                   class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition flex items-center gap-2 font-medium">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                <div class="flex gap-3">
                    <button type="button" onclick="printReceipt()" 
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2 font-medium">
                        <i class="fas fa-print"></i> Imprimir Comprovante
                    </button>
                    <button type="button" onclick="confirmDelivery()" id="deliverBtn" disabled
                            class="px-8 py-3 bg-gray-300 text-gray-500 rounded-lg transition flex items-center gap-2 font-medium cursor-not-allowed">
                        <i class="fas fa-check-circle"></i> Finalizar Entrega
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const checkboxes = ['check_keys', 'check_damage', 'check_payment', 'check_document'];
    const deliverBtn = document.getElementById('deliverBtn');

    function updateDeliverButton() {
        const allChecked = checkboxes.every(id => document.getElementById(id).checked);
        
        if (allChecked) {
            deliverBtn.disabled = false;
            deliverBtn.classList.remove('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
            deliverBtn.classList.add('bg-green-600', 'text-white', 'hover:bg-green-700', 'cursor-pointer');
        } else {
            deliverBtn.disabled = true;
            deliverBtn.classList.remove('bg-green-600', 'text-white', 'hover:bg-green-700', 'cursor-pointer');
            deliverBtn.classList.add('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
        }
    }

    checkboxes.forEach(id => {
        document.getElementById(id).addEventListener('change', updateDeliverButton);
    });

    function confirmDelivery() {
        if (!deliverBtn.disabled) {
            if (confirm('Tem certeza que deseja finalizar a entrega?\n\nConfirme se:\n✓ Chaves foram devolvidas\n✓ Cliente aprovou o serviço\n✓ Pagamento foi confirmado\n✓ Comprovante foi entregue')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = <?php echo json_encode(route('operator.orders.deliver', $order), 512) ?>;
                const csrf = document.createElement('input');
                csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = document.querySelector('meta[name="csrf-token"]').content;
                form.appendChild(csrf);
                document.body.appendChild(form);
                form.submit();
            }
        }
    }

    function printReceipt() {
        window.open('<?php echo e(route("operator.orders.receipt", $order)); ?>', '_blank');
    }
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/pedro/Desktop/MerchOnline/carwash/carwash_express/resources/views/operator/deliver-order.blade.php ENDPATH**/ ?>