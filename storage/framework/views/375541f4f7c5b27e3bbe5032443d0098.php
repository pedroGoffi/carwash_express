<?php
    $editing = isset($user);
    $val = fn ($field,$default='') => old($field, $editing ? ($user->{$field} ?? $default) : $default);
?>
<form method="POST" action="<?php echo e($editing ? route('manager.users.update',$user) : route('manager.users.store')); ?>" class="space-y-5">
    <?php echo csrf_field(); ?> <?php if($editing): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
    <?php if($errors->any()): ?><div class="cw-error" role="alert"><strong>Confira os campos:</strong><ul class="list-disc ml-5"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul></div><?php endif; ?>
    <section class="cw-card p-5 sm:p-6 space-y-4">
        <div><h2 class="text-base font-bold text-slate-900"><i class="fas fa-address-card text-blue-600 mr-2"></i>Identificação do funcionário</h2><p class="text-xs text-slate-500 mt-1">Cadastro e contato em uma única etapa.</p></div>
        <div class="grid sm:grid-cols-2 gap-4">
            <label class="cw-field sm:col-span-2">Nome completo <span class="text-red-500">*</span><input class="cw-input" name="name" maxlength="255" required autofocus autocomplete="name" value="<?php echo e($val('name')); ?>" placeholder="Nome utilizado também no acesso"></label>
            <label class="cw-field">Telefone<input class="cw-input" name="phone" maxlength="32" value="<?php echo e($val('phone')); ?>" placeholder="(00) 00000-0000"></label>
            <label class="cw-field">E-mail de contato<input class="cw-input" type="email" name="email_contact" maxlength="255" value="<?php echo e($val('email_contact')); ?>" placeholder="email@exemplo.com"></label>
            <label class="cw-field">CPF / documento<input class="cw-input" name="document" maxlength="32" value="<?php echo e($val('document')); ?>" placeholder="Documento do colaborador"></label>
            <label class="cw-field">Função<input class="cw-input" name="job_title" maxlength="100" value="<?php echo e($val('job_title','Operador')); ?>" placeholder="Operador"></label>
            <label class="cw-field">Vínculo
                <select class="cw-input" name="employment_type"><option value="">Não informado</option>
                    <?php $__currentLoopData = ['clt'=>'CLT','daily'=>'Diarista','contract'=>'Contrato','other'=>'Outro']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code=>$label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($code); ?>" <?php if($val('employment_type')===$code): echo 'selected'; endif; ?>><?php echo e($label); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>
            <label class="cw-field">Data de admissão<input class="cw-input" type="date" name="hired_at" value="<?php echo e(old('hired_at', $editing ? $user->hired_at?->format('Y-m-d') : '')); ?>"></label>
        </div>
    </section>
    <section class="cw-card p-5 sm:p-6 space-y-4">
        <div><h2 class="font-bold text-slate-900"><i class="fas fa-money-bill-wave text-blue-600 mr-2"></i>Remuneração e pagamento</h2><p class="text-xs text-slate-500 mt-1">Preencha mensal OU diária. Se o valor diário for maior que zero, o cálculo mensal usa valor diário × dias.</p></div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <label class="cw-field">Salário mensal (R$)<input class="cw-input" type="number" step="0.01" min="0" name="monthly_salary" value="<?php echo e($val('monthly_salary','0.00')); ?>"></label>
            <label class="cw-field">Diária (R$)<input class="cw-input" type="number" step="0.01" min="0" name="daily_rate" value="<?php echo e($val('daily_rate','0.00')); ?>"></label>
            <label class="cw-field">Dias trabalhados (mês)<input class="cw-input" type="number" min="0" max="31" name="days_worked" value="<?php echo e($val('days_worked','0')); ?>"></label>
            <label class="cw-field">Chave PIX<input class="cw-input" name="pix_key" maxlength="255" value="<?php echo e($val('pix_key')); ?>" placeholder="Opcional"></label>
        </div>
        <div class="rounded-xl bg-blue-50 border border-blue-100 text-blue-900 text-sm p-3">Vales, consumo de produtos, encargos e outras despesas são registrados em <a class="font-semibold underline" href="<?php echo e(route('manager.payroll.index')); ?>">Folha e custos</a>. Não precisam ser cadastrados aqui.</div>
    </section>
    <section class="cw-card p-5 sm:p-6 space-y-4">
        <div><h2 class="font-bold text-slate-900"><i class="fas fa-shield-halved text-blue-600 mr-2"></i>Acesso e observações</h2><p class="text-xs text-slate-500 mt-1">Acesso do operador. A senha não fica visível depois de cadastrada.</p></div>
        <div class="grid sm:grid-cols-2 gap-4">
            <label class="cw-field"><?php echo e($editing ? 'Nova senha (opcional)' : 'Senha de acesso *'); ?><input class="cw-input" type="password" name="password" autocomplete="new-password" minlength="8" <?php echo e($editing ? '' : 'required'); ?>></label>
            <label class="cw-field">Confirmar senha<input class="cw-input" type="password" name="password_confirmation" autocomplete="new-password" minlength="8" <?php echo e($editing ? '' : 'required'); ?>></label>
        </div>
        <label class="cw-field">Observações internas<textarea class="cw-input min-h-24" name="staff_notes" maxlength="3000" placeholder="Dados relevantes para a gestão da equipe"><?php echo e($val('staff_notes')); ?></textarea></label>
        <label class="inline-flex gap-3 items-center text-sm font-medium text-slate-700"><input type="checkbox" class="rounded border-slate-300 text-blue-600" name="active" value="1" <?php if(old('active', $editing ? (bool)$user->active : true)): echo 'checked'; endif; ?>>Funcionário ativo</label>
    </section>
    <div class="flex flex-wrap gap-3 justify-end"><a class="cw-btn-secondary" href="<?php echo e(route('manager.users.index')); ?>">Cancelar</a><button class="cw-btn-primary" type="submit"><i class="fas fa-check mr-2"></i><?php echo e($editing ? 'Salvar alterações' : 'Cadastrar funcionário'); ?></button></div>
</form>
<?php /**PATH /home/pedro/Desktop/MerchOnline/carwash/carwash_express/resources/views/manager/users/_form.blade.php ENDPATH**/ ?>