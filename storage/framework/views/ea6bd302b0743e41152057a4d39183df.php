<?php $__env->startSection('title','Equipe'); ?>
<?php $__env->startSection('header','Equipe'); ?>
<?php $__env->startSection('subheader','Funcionários, informações e remuneração organizados'); ?>
<?php $__env->startSection('content'); ?>
<div class="space-y-5">
    <?php if(session('success')): ?><div class="cw-message" role="status"><?php echo e(session('success')); ?></div><?php endif; ?>
    <div class="cw-card p-4 flex flex-col sm:flex-row gap-3 justify-between sm:items-center">
        <form class="flex items-center gap-2 w-full sm:max-w-md" method="GET"><input class="cw-input" type="search" name="q" value="<?php echo e(request('q')); ?>" placeholder="Buscar pelo nome"><button class="cw-btn-secondary" type="submit">Buscar</button></form>
        <a href="<?php echo e(route('manager.users.create')); ?>" class="cw-btn-primary"><i class="fas fa-user-plus mr-2"></i>Novo funcionário</a>
    </div>
    <div class="cw-card overflow-hidden"><div class="overflow-x-auto"><table class="cw-table"><thead><tr><th>Funcionário</th><th>Função / vínculo</th><th>Salário base</th><th>Situação</th><th class="text-right">Ações</th></tr></thead><tbody>
    <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <tr><td><div class="font-semibold text-slate-900"><?php echo e($user->name); ?></div><div class="text-xs text-slate-500"><?php echo e($user->phone ?: ($user->email_contact ?: 'Sem contato cadastrado')); ?></div></td>
    <td><div><?php echo e($user->job_title ?: 'Operador'); ?></div><div class="text-xs text-slate-500"><?php echo e(['clt'=>'CLT','daily'=>'Diarista','contract'=>'Contrato','other'=>'Outro'][$user->employment_type] ?? 'Não informado'); ?></div></td>
    <td class="whitespace-nowrap"><?php echo e((float)$user->daily_rate > 0 ? 'R$ '.number_format($user->daily_rate,2,',','.').'/dia' : 'R$ '.number_format($user->monthly_salary,2,',','.') . '/mês'); ?></td>
    <td><span class="cw-pill <?php echo e($user->active ? 'cw-pill-success' : 'cw-pill-muted'); ?>"><?php echo e($user->active ? 'Ativo' : 'Inativo'); ?></span></td>
    <td class="text-right whitespace-nowrap"><a class="cw-link mr-3" href="<?php echo e(route('manager.payroll.index',['user_id'=>$user->id])); ?>">Custos</a><a class="cw-link mr-3" href="<?php echo e(route('manager.users.edit',$user)); ?>">Editar</a><?php if($user->active): ?><form class="inline" action="<?php echo e(route('manager.users.destroy',$user)); ?>" method="POST" onsubmit="return confirm('Desativar este colaborador? O histórico será preservado.')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="text-rose-600 font-semibold text-sm hover:underline">Desativar</button></form><?php endif; ?></td></tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="5" class="py-12 text-center text-slate-500">Nenhum funcionário encontrado.</td></tr><?php endif; ?>
    </tbody></table></div><div class="p-4"><?php echo e($users->links()); ?></div></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/pedro/Desktop/MerchOnline/carwash/carwash_express/resources/views/manager/users/index.blade.php ENDPATH**/ ?>