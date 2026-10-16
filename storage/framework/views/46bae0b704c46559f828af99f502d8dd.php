<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Recibo - OS <?php echo e($order->code); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            width: 80mm;
            height: 80mm;
            margin: 0;
            padding: 1mm;
        }
        
        .receipt {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        /* HEADER */
        .header {
            text-align: center;
            border-bottom: 2px solid black;
            padding-bottom: 2px;
            margin-bottom: 2px;
        }
        .store-name { font-size: 11px; font-weight: 900; }
        .store-info { font-size: 6px; font-weight: bold; }
        
        /* CODIGO */
        .code-section {
            text-align: center;
            margin: 2px 0;
        }
        .code-value {
            font-size: 22px;
            font-weight: 900;
            letter-spacing: 2px;
            padding: 2px;
        }
        .plate-value {
            font-size: 9px;
            font-weight: 900;
        }
        
        /* INFO CLIENTE */
        .info-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 2px;
            margin: 2px 0;
        }
        .info-card {
            padding: 2px 4px;
            flex: 1;
            border-left: 3px solid black;
        }
        .info-label { font-size: 5px; font-weight: 900; text-transform: uppercase; }
        .info-value { font-size: 9px; font-weight: 900; }
        
        /* SERVICO PRINCIPAL - SEM BACKGROUND, APENAS NEGRITO */
        .main-service {
            font-weight: 900;
            padding: 4px;
            margin: 4px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid black;
            border-top: 2px solid black;
        }
        .main-service-name { font-size: 22px; font-weight: 900; letter-spacing: 1px; }
        .main-service-price { font-size: 22px; font-weight: 900; }
        
        /* LISTA DE SERVICOS - 2 COLUNAS */
        .services-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2px 4px;
            margin: 4px 0;
        }
        .service-item {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            font-weight: 900;
            padding: 1px 0;
            border-bottom: 1px dotted #ccc;
        }
        .service-name {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 45px;
        }
        .service-price {
            font-weight: 900;
            white-space: nowrap;
        }
        
        /* TOTAL */
        .total-box {
            display: flex;
            justify-content: space-between;
            padding: 3px 5px;
            margin: 3px 0;
            border-top: 2px solid black;
            border-bottom: 2px solid black;
        }
        .total-label { font-size: 14px; font-weight: 900; }
        .total-value { font-size: 18px; font-weight: 900; }
        
        /* FOOTER */
        .footer {
            text-align: center;
            border-top: 1px solid black;
            padding-top: 2px;
            margin-top: 2px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .cut { text-align: center; font-size: 7px; letter-spacing: 2px; margin-top: 2px; font-weight: bold; }
        
        @media print {
            @page { size: 80mm 80mm landscape; margin: 0; }
            body { margin: 0; padding: 1mm; width: 80mm; height: 80mm; }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- HEADER -->
        <div class="header">
            <div class="store-name">CAR WASH EXPRESS</div>
            <div class="store-info">Rua Independência, 60 | (54) 99638-0894</div>
        </div>

        <!-- CODIGO -->
        <div class="code-section">
            <div class="code-value"><?php echo e($order->code); ?></div>
            <div class="plate-value"><?php echo e($order->license_plate ?? 'SEM PLACA'); ?></div>
        </div>

        <!-- INFO CLIENTE -->
        <div class="info-grid">
            <div class="info-card">
                <div class="info-label">CLIENTE</div>
                <div class="info-value"><?php echo e(substr($order->customer_name ?? 'ANONIMO', 0, 15)); ?></div>
            </div>
            <div class="info-card">
                <div class="info-label">ENTRADA</div>
                <div class="info-value"><?php echo e($order->created_at->format('d/m H:i')); ?></div>
            </div>
            <div class="info-card">
                <div class="info-label">SAIDA</div>
                <div class="info-value"><?php echo e($order->updated_at->format('d/m H:i')); ?></div>
            </div>
            <div class="info-card">
                <div class="info-label">PAGAMENTO</div>
                <div class="info-value">
                    <?php if($order->payments && $order->payments->count() > 0): ?>
                        <?php echo e(strtoupper($order->payments[0]->method)); ?>

                    <?php else: ?>
                        PENDENTE
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- SERVICO PRINCIPAL -->
        <?php $primeiro = $order->products->first(fn ($item) => $item->type?->value === 'wash') ?? $order->products->first(); ?>
        <div class="main-service">
            <div class="main-service-name"><?php echo e(strtoupper($primeiro?->name ?? 'SERVIÇO')); ?></div>
            <h5>R$</h5>
            <div class="main-service-price"><?php echo e(number_format($primeiro?->pivot?->total_price ?? 0, 2, ',', '.')); ?></div>
        </div>

        <!-- DEMAIS SERVICOS - 2 POR LINHA -->
        <?php if($order->products->count() > 1): ?>
        <div class="services-grid">
            <?php $__currentLoopData = $order->products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($primeiro && $item->id !== $primeiro->id): ?>
                <div class="service-item">
                    <div class="service-name" title="<?php echo e($item->name); ?>"><?php echo e($item->pivot->quantity); ?>× <?php echo e(substr($item->name, 0, 12)); ?></div>
                    <div class="service-price">R$ <?php echo e(number_format($item->pivot->total_price, 2, ',', '.')); ?></div>
                </div>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php endif; ?>

        <!-- TOTAL -->
        <div class="total-box">
            <div class="total-label">TOTAL</div>
            <div class="total-value">R$ <?php echo e(number_format($order->total, 2, ',', '.')); ?></div>
        </div>

        <!-- FOOTER -->
        <div class="footer">
            <div>Sistema criado por MerchOnline - Pedro Henrique Goffi de Paulo</div>
            <div><?php echo e(now()->format('d/m/Y H:i:s')); ?></div>
        </div>

        <div class="cut">• • • • • • • • • • • • • •</div>
    </div>
    
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
                setTimeout(function() {
                    window.close();
                }, 500);
            }, 100);
        };
    </script>
</body>
</html><?php /**PATH /home/pedro/Desktop/MerchOnline/carwash/carwash_express/resources/views/operator/receipt.blade.php ENDPATH**/ ?>