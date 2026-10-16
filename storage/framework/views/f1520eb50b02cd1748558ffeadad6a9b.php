<?php $__env->startSection('title', 'Anotações'); ?>
<?php $__env->startSection('header', 'Anotações da equipe'); ?>
<?php $__env->startSection('subheader', 'Registros e solicitações da operação'); ?>
<?php $__env->startSection('content'); ?>
<div class="space-y-4">
    <?php if(session('success')): ?><div class="rounded-xl bg-emerald-50 p-4 text-emerald-800"><?php echo e(session('success')); ?></div><?php endif; ?>
    <?php $__empty_1 = true; $__currentLoopData = $notes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap gap-2 items-center mb-2"><span class="text-sm font-semibold text-slate-900"><?php echo e($note->user?->name ?? 'Colaborador'); ?></span>
              <?php if($note->is_pinned): ?><span class="text-xs rounded-full bg-blue-50 text-blue-700 px-2 py-1">Fixada</span><?php endif; ?>
              <?php if($note->type === 'cancel_request'): ?><span class="text-xs rounded-full bg-amber-50 text-amber-700 px-2 py-1">Solicitação de cancelamento</span><?php endif; ?>
            </div>
            <p class="whitespace-pre-wrap break-words text-sm text-slate-700"><?php echo e($note->content); ?></p>
            <p class="text-xs text-slate-400 mt-3"><?php echo e($note->created_at?->format('d/m/Y H:i')); ?></p>
          </div>
          <div class="flex items-center gap-2">
            <form method="POST" action="<?php echo e(route('manager.notes.pin', $note)); ?>"><?php echo csrf_field(); ?><button class="text-sm rounded-lg border border-slate-200 px-3 py-2 hover:bg-slate-50"><?php echo e($note->is_pinned ? 'Desfixar' : 'Fixar'); ?></button></form>
            <form method="POST" action="<?php echo e(route('manager.notes.destroy', $note)); ?>" onsubmit="return confirm('Excluir anotação?')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="text-sm rounded-lg border border-red-100 text-red-700 px-3 py-2 hover:bg-red-50">Excluir</button></form>
          </div>
        </div>
      </article>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <div class="rounded-2xl border border-slate-200 bg-white p-12 text-center text-slate-500">Nenhuma anotação registrada.</div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/pedro/Desktop/MerchOnline/carwash/carwash_express/resources/views/manager/notes/index.blade.php ENDPATH**/ ?>