<?php $__env->startSection('title','Folha e custos'); ?>
<?php $__env->startSection('header','Folha e custos'); ?>
<?php $__env->startSection('subheader','Salários, descontos e despesas da equipe em um único lugar'); ?>
<?php $__env->startSection('content'); ?>
<?php
    $month = $period->format('Y-m');
    $employee = $userId ? $team->firstWhere('id', $userId) : null;
    $cats = ['benefit'=>'Benefícios','transport'=>'Transporte','meal'=>'Alimentação','tax'=>'Encargos','bonus'=>'Bonificação','equipment'=>'Equipamentos','other'=>'Outros'];
    $tabLink = fn ($t) => route('manager.payroll.index', array_filter(['month'=>$month,'user_id'=>$userId,'tab'=>$t]));
?>
<div class="space-y-5">
    <div class="cw-card p-5">
        <div class="flex flex-col lg:flex-row justify-between lg:items-center gap-4">
            <div>
                <p class="cw-eyebrow">Competência financeira</p>
                <h2 class="text-xl font-bold text-slate-900 mt-1"><?php echo e($period->locale('pt_BR')->translatedFormat('F \d\e Y')); ?></h2>
                <p class="text-sm text-slate-500 mt-1"><?php echo e($employee ? 'Custos exclusivos de '.$employee->name : 'Visão geral de colaboradores ativos'); ?> · <?php echo e($closedForPeriod ? 'Fechamento registrado' : 'Competência aberta'); ?></p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2">
                <form method="GET" action="<?php echo e(route('manager.payroll.index')); ?>" class="flex flex-wrap items-center gap-2">
                    <input type="month" name="month" value="<?php echo e($month); ?>" aria-label="Competência" class="cw-input max-w-44" required>
                    <select name="user_id" aria-label="Filtrar colaborador" class="cw-input max-w-64">
                        <option value="">Todos os colaboradores</option>
                        <?php $__currentLoopData = $team; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $person): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($person->id); ?>" <?php if($userId == $person->id): echo 'selected'; endif; ?>><?php echo e($person->name); ?><?php echo e(!$person->active ? ' (inativo)' : ''); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <input type="hidden" name="tab" value="<?php echo e($tab); ?>">
                    <button class="cw-btn-secondary" type="submit"><i class="fas fa-filter mr-2"></i>Filtrar</button>
                </form>
                <a href="<?php echo e(route('manager.users.create')); ?>" class="cw-btn-primary"><i class="fas fa-user-plus mr-2"></i>Colaborador</a>
            </div>
        </div>
    </div>
    <?php if(session('success')): ?><div class="cw-message" role="status"><i class="fas fa-circle-check"></i> <?php echo e(session('success')); ?></div><?php endif; ?>
    <?php if($errors->any()): ?><div class="cw-error" role="alert"><strong>Verifique os campos:</strong> <?php echo e($errors->first()); ?></div><?php endif; ?>

    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3">
        <?php $__currentLoopData = [
            ['Folha base','base','fa-wallet'],['Vales','advances','fa-hand-holding-dollar'],
            ['Consumos','consumptions','fa-mug-hot'],['Outros custos','extras','fa-receipt'],
            ['Líquido da folha','net','fa-money-bill-wave'],['Custo estimado','employer','fa-chart-line']
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label,$key,$icon]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="cw-card p-4">
                <div class="flex items-center gap-2 text-slate-500 text-xs"><i class="fas <?php echo e($icon); ?> text-blue-500"></i><?php echo e($label); ?></div>
                <p class="text-xl font-bold text-slate-900 mt-3 tabular-nums">R$ <?php echo e(number_format($summary[$key],2,',','.')); ?></p>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <p class="text-xs text-slate-500">Custo estimado = folha base + despesas extras lançadas. Vales e consumos já reduzem o líquido, não são somados novamente ao custo. Encargos legais só entram se forem lançados em despesas extras.</p>
    <nav class="cw-card flex gap-2 p-2 overflow-x-auto" aria-label="Seções financeiras">
        <a href="<?php echo e($tabLink('overview')); ?>" class="cw-tab <?php echo e($tab==='overview' ? 'cw-tab-active' : ''); ?>"><i class="fas fa-users mr-2"></i>Equipe e lançamentos</a>
        <a href="<?php echo e($tabLink('movements')); ?>" class="cw-tab <?php echo e($tab==='movements' ? 'cw-tab-active' : ''); ?>"><i class="fas fa-list mr-2"></i>Extrato de custos</a>
        <a href="<?php echo e($tabLink('history')); ?>" class="cw-tab <?php echo e($tab==='history' ? 'cw-tab-active' : ''); ?>"><i class="fas fa-clock-rotate-left mr-2"></i>Histórico fechado</a>
    </nav>

    <?php if($tab==='overview'): ?>
        <div class="grid lg:grid-cols-3 gap-4">
            <form action="<?php echo e(route('manager.advances.store')); ?>" method="POST" class="cw-card p-5 space-y-3"><?php echo csrf_field(); ?>
                <h3 class="font-bold text-slate-900"><i class="fas fa-money-bill-transfer text-blue-600 mr-2"></i>Vale em dinheiro</h3>
                <p class="text-xs text-slate-500">Desconto da folha do colaborador.</p>
                <select name="user_id" required class="cw-input"><option value="">Escolha o colaborador</option><?php $__currentLoopData = $team->where('active',true); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $person): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($person->id); ?>" <?php if(old('user_id',$userId)==$person->id): echo 'selected'; endif; ?>><?php echo e($person->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
                <div class="grid grid-cols-2 gap-2"><input class="cw-input" name="amount" aria-label="Valor do vale" type="number" step="0.01" min="0.01" placeholder="R$ 0,00" required><input class="cw-input" name="date" aria-label="Data do vale" type="date" value="<?php echo e($period->isCurrentMonth()?now()->toDateString():$period->toDateString()); ?>" required></div>
                <input class="cw-input" name="reason" maxlength="500" placeholder="Motivo (opcional)">
                <button class="cw-btn-primary w-full" type="submit">Registrar vale</button>
            </form>
            <form action="<?php echo e(route('manager.consumptions.store')); ?>" method="POST" class="cw-card p-5 space-y-3"><?php echo csrf_field(); ?>
                <h3 class="font-bold text-slate-900"><i class="fas fa-mug-hot text-blue-600 mr-2"></i>Consumo de produto</h3>
                <p class="text-xs text-slate-500">Produto consumido descontado da folha.</p>
                <select name="user_id" required class="cw-input"><option value="">Escolha o colaborador</option><?php $__currentLoopData = $team->where('active',true); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $person): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($person->id); ?>" <?php if(old('user_id',$userId)==$person->id): echo 'selected'; endif; ?>><?php echo e($person->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
                <select class="cw-input" name="product_id" required><option value="">Selecione o produto</option><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($product->id); ?>"><?php echo e($product->name); ?> · R$ <?php echo e(number_format($product->getPriceForVehicleType('normal'),2,',','.')); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
                <div class="grid grid-cols-2 gap-2"><input class="cw-input" type="number" min="1" max="100" name="quantity" aria-label="Quantidade" value="1" required><input class="cw-input" name="date" type="date" aria-label="Data do consumo" value="<?php echo e($period->isCurrentMonth()?now()->toDateString():$period->toDateString()); ?>" required></div>
                <button class="cw-btn-primary w-full" type="submit">Registrar consumo</button>
            </form>
            <form action="<?php echo e(route('manager.expenses.store')); ?>" method="POST" class="cw-card p-5 space-y-3"><?php echo csrf_field(); ?>
                <h3 class="font-bold text-slate-900"><i class="fas fa-file-invoice-dollar text-blue-600 mr-2"></i>Despesa do funcionário</h3>
                <p class="text-xs text-slate-500">Custo extra; não reduz o salário líquido.</p>
                <select name="user_id" required class="cw-input"><option value="">Escolha o colaborador</option><?php $__currentLoopData = $team->where('active',true); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $person): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($person->id); ?>" <?php if(old('user_id',$userId)==$person->id): echo 'selected'; endif; ?>><?php echo e($person->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
                <select name="category" required class="cw-input"><option value="">Categoria</option><?php $__currentLoopData = $cats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code=>$text): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($code); ?>"><?php echo e($text); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
                <div class="grid grid-cols-2 gap-2"><input class="cw-input" name="amount" type="number" step="0.01" min="0.01" aria-label="Valor da despesa" placeholder="R$ 0,00" required><input class="cw-input" name="date" type="date" aria-label="Data da despesa" value="<?php echo e($period->isCurrentMonth()?now()->toDateString():$period->toDateString()); ?>" required></div>
                <input class="cw-input" name="description" maxlength="500" placeholder="Descrição (opcional)">
                <button class="cw-btn-primary w-full" type="submit">Adicionar despesa</button>
            </form>
        </div>
        <section class="cw-card overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3"><div><h3 class="font-bold text-slate-900">Custos por colaborador</h3><p class="text-xs text-slate-500 mt-1">Clique em “Ver gastos” para isolar o extrato de uma pessoa.</p></div><a href="<?php echo e(route('manager.users.index')); ?>" class="cw-btn-secondary">Gerenciar equipe</a></div>
            <div class="overflow-x-auto"><table class="cw-table"><thead><tr><th>Colaborador</th><th class="text-right">Salário base</th><th class="text-right">Vales</th><th class="text-right">Consumos</th><th class="text-right">Outros custos</th><th class="text-right">Líquido</th><th class="text-right">Ações</th></tr></thead>
            <tbody><?php $__empty_1 = true; $__currentLoopData = $workers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $worker): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr><td><div class="font-semibold text-slate-900"><?php echo e($worker->name); ?></div><div class="text-xs text-slate-500"><?php echo e((float)$worker->daily_rate>0 ? $worker->days_worked.' dias × R$ '.number_format($worker->daily_rate,2,',','.') : 'Mensalista'); ?></div></td>
                <td class="text-right whitespace-nowrap">R$ <?php echo e(number_format($worker->base_amount,2,',','.')); ?></td>
                <td class="text-right whitespace-nowrap">R$ <?php echo e(number_format($worker->money_advances,2,',','.')); ?></td>
                <td class="text-right whitespace-nowrap">R$ <?php echo e(number_format($worker->product_consumptions,2,',','.')); ?></td>
                <td class="text-right whitespace-nowrap">R$ <?php echo e(number_format($worker->extra_costs,2,',','.')); ?></td>
                <td class="text-right whitespace-nowrap font-bold text-slate-900">R$ <?php echo e(number_format($worker->net_amount,2,',','.')); ?></td>
                <td class="text-right whitespace-nowrap"><a class="text-sm font-semibold text-blue-600 hover:underline mr-3" href="<?php echo e(route('manager.payroll.index',['month'=>$month,'user_id'=>$worker->id,'tab'=>'movements'])); ?>">Ver gastos</a><a class="text-sm font-semibold text-slate-600 hover:underline" href="<?php echo e(route('manager.users.edit',$worker)); ?>">Editar cadastro</a></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="7" class="text-center text-slate-500 py-10">Nenhum colaborador para o filtro escolhido.</td></tr><?php endif; ?></tbody></table></div>
        </section>
        <div class="cw-card p-5 flex flex-col sm:flex-row gap-4 sm:items-center justify-between">
            <div><h3 class="font-semibold text-slate-900">Fechamento mensal</h3><p class="text-xs text-slate-500 mt-1">Registra os valores atuais da folha. Revise a competência antes de confirmar.</p></div>
            <form method="POST" action="<?php echo e(route('manager.payroll.close-period')); ?>" onsubmit="return confirm('Fechar a competência? Um novo fechamento atualiza o histórico desse mês.')"><?php echo csrf_field(); ?><input type="hidden" name="month" value="<?php echo e($month); ?>"><button class="cw-btn-primary"><i class="fas fa-lock mr-2"></i><?php echo e($closedForPeriod ? 'Atualizar fechamento' : 'Fechar competência'); ?></button></form>
        </div>
    <?php elseif($tab==='movements'): ?>
        <section class="cw-card overflow-hidden"><div class="p-5 border-b border-slate-100"><h3 class="font-bold text-slate-900">Vales e consumos <?php echo e($employee ? 'de '.$employee->name : ''); ?></h3><p class="text-xs text-slate-500 mt-1">Lançamentos que reduzem o valor líquido da folha.</p></div>
        <div class="overflow-x-auto"><table class="cw-table"><thead><tr><th>Data</th><th>Funcionário</th><th>Categoria</th><th>Detalhe</th><th class="text-right">Valor</th><th class="text-right">Ação</th></tr></thead><tbody>
        <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td><?php echo e($t->date->format('d/m/Y')); ?></td><td><?php echo e($t->user?->name); ?></td><td><?php echo e($t->type === 'product' ? 'Consumo' : 'Vale'); ?></td><td><?php echo e($t->type === 'product' ? ($t->product?->name ?? 'Produto').' × '.$t->quantity : ($t->reason ?: 'Adiantamento')); ?></td><td class="text-right font-semibold">R$ <?php echo e(number_format($t->amount,2,',','.')); ?></td><td class="text-right"><form method="POST" action="<?php echo e($t->type === 'product' ? route('manager.consumptions.destroy',$t) : route('manager.advances.destroy',$t)); ?>" onsubmit="return confirm('Excluir lançamento?')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="cw-action-danger" aria-label="Remover lançamento"><i class="fas fa-trash"></i></button></form></td></tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="6" class="text-center py-8 text-slate-500">Nenhum vale ou consumo nesta competência.</td></tr><?php endif; ?>
        </tbody></table></div><div class="p-4"><?php echo e($transactions->links()); ?></div></section>
        <section class="cw-card overflow-hidden"><div class="p-5 border-b border-slate-100"><h3 class="font-bold text-slate-900">Despesas extras <?php echo e($employee ? 'de '.$employee->name : ''); ?></h3><p class="text-xs text-slate-500 mt-1">Benefícios, transporte, alimentação, encargos e demais custos por colaborador.</p></div>
        <div class="overflow-x-auto"><table class="cw-table"><thead><tr><th>Data</th><th>Funcionário</th><th>Categoria</th><th>Descrição</th><th class="text-right">Valor</th><th class="text-right">Ação</th></tr></thead><tbody>
        <?php $__empty_1 = true; $__currentLoopData = $expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td><?php echo e($e->date->format('d/m/Y')); ?></td><td><?php echo e($e->user?->name); ?></td><td><?php echo e($cats[$e->category] ?? $e->category); ?></td><td><?php echo e($e->description ?: 'Sem observação'); ?></td><td class="text-right font-semibold">R$ <?php echo e(number_format($e->amount,2,',','.')); ?></td><td class="text-right"><form method="POST" action="<?php echo e(route('manager.expenses.destroy',$e)); ?>" onsubmit="return confirm('Excluir despesa?')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="cw-action-danger" aria-label="Remover despesa"><i class="fas fa-trash"></i></button></form></td></tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="6" class="text-center py-8 text-slate-500">Nenhuma despesa extra nesta competência.</td></tr><?php endif; ?>
        </tbody></table></div><div class="p-4"><?php echo e($expenses->links()); ?></div></section>
    <?php else: ?>
        <section class="cw-card overflow-hidden"><div class="p-5 border-b border-slate-100"><h3 class="font-bold text-slate-900">Histórico de folhas fechadas</h3><p class="text-xs text-slate-500">Carregado somente ao abrir esta seção.</p></div><div class="overflow-x-auto"><table class="cw-table"><thead><tr><th>Competência</th><th>Colaborador</th><th class="text-right">Base</th><th class="text-right">Vales</th><th class="text-right">Consumos</th><th class="text-right">Líquido</th><th class="text-right">Outros custos</th><th class="text-right">Custo da empresa</th><th>Fechado em</th></tr></thead><tbody>
        <?php $__empty_1 = true; $__currentLoopData = $history; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td><?php echo e($h->period?->format('m/Y')); ?></td><td><?php echo e($h->user?->name); ?></td><td class="text-right">R$ <?php echo e(number_format($h->base_amount,2,',','.')); ?></td><td class="text-right">R$ <?php echo e(number_format($h->advances_amount,2,',','.')); ?></td><td class="text-right">R$ <?php echo e(number_format($h->consumptions_amount,2,',','.')); ?></td><td class="text-right font-semibold">R$ <?php echo e(number_format($h->net_amount,2,',','.')); ?></td><td class="text-right">R$ <?php echo e(number_format($h->extra_costs_amount,2,',','.')); ?></td><td class="text-right"><?php echo e($h->employer_cost_amount > 0 ? 'R$ '.number_format($h->employer_cost_amount,2,',','.') : '—'); ?></td><td><?php echo e($h->closed_at?->format('d/m/Y')); ?></td></tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="9" class="text-center py-10 text-slate-500">Sem fechamento registrado.</td></tr><?php endif; ?>
        </tbody></table></div><div class="p-4"><?php echo e($history->links()); ?></div></section>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/pedro/Desktop/MerchOnline/carwash/carwash_express/resources/views/manager/payroll/index.blade.php ENDPATH**/ ?>