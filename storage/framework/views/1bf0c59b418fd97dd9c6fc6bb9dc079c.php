<?php $__env->startSection('title','Editar funcionário'); ?>
<?php $__env->startSection('header','Editar funcionário'); ?>
<?php $__env->startSection('subheader','Informações, contato, remuneração e acesso'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-5xl mx-auto"><div class="flex justify-end mb-4"><a class="cw-btn-secondary" href="<?php echo e(route('manager.payroll.index',['user_id'=>$user->id])); ?>"><i class="fas fa-chart-line mr-2"></i>Custos deste funcionário</a></div><?php echo $__env->make('manager.users._form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/pedro/Desktop/MerchOnline/carwash/carwash_express/resources/views/manager/users/edit.blade.php ENDPATH**/ ?>