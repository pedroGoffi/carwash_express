<?php

namespace App\Services\Operator;

use App\Enums\PaymentMethod;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/** Resumo de um dia: ordens e itens pela data de criação; recebimentos pela data do pagamento. */
class TodayOrderSummaryService
{
    public function forCompany(int $companyId, ?string $date = null): array
    {
        // Dia interpretado no fuso horario do negocio (configurado em app.timezone).
        $day = $date ? CarbonImmutable::createFromFormat('!Y-m-d', $date, config('app.timezone')) : CarbonImmutable::now(config('app.timezone'));
        $start = $day->startOfDay();
        $end = $day->endOfDay();

        $orders = DB::table('service_orders')
            ->where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$start, $end]);

        $saleSummary = (clone $orders)->where('order_type', 'sale')
            ->selectRaw('COUNT(*) AS quantity, COALESCE(SUM(total), 0) AS amount')->first();
        $vehicles = ['normal' => 0, 'suv' => 0, 'pickup' => 0];
        foreach ((clone $orders)->where('order_type', 'service')->select('vehicle_type')->selectRaw('COUNT(*) AS quantity')->groupBy('vehicle_type')->get() as $row) {
            if (array_key_exists($row->vehicle_type, $vehicles)) $vehicles[$row->vehicle_type] = (int) $row->quantity;
        }

        // Pagamentos de hoje, inclusive de ordens abertas em dias anteriores.
        // Sempre vincular ao tenant e desconsiderar ordens canceladas.
        $payments = [];
        foreach (PaymentMethod::cases() as $method) {
            $payments[$method->value] = ['label' => $method->label(), 'quantity' => 0, 'amount' => 0.0];
        }
        $received = DB::table('payments as pay')
            ->join('service_orders as so', 'so.id', '=', 'pay.service_order_id')
            ->where('so.company_id', $companyId)
            ->where('so.status', '!=', 'cancelled')
            ->whereBetween('pay.created_at', [$start, $end])
            ->select('pay.method')->selectRaw('COUNT(*) AS quantity, SUM(pay.amount) AS amount')
            ->groupBy('pay.method')->get();
        $receivedTotal = 0.0;
        foreach ($received as $row) {
            if (!isset($payments[$row->method])) continue;
            $payments[$row->method]['quantity'] = (int) $row->quantity;
            $payments[$row->method]['amount'] = round((float) $row->amount, 2);
            $receivedTotal += (float) $row->amount;
        }

        $itemQuery = fn (string $type) => DB::table('service_order_items as item')
            ->join('service_orders as so', 'so.id', '=', 'item.service_order_id')
            ->join('products as product', 'product.id', '=', 'item.product_id')
            ->where('so.company_id', $companyId)
            ->where('so.status', '!=', 'cancelled')
            ->whereBetween('so.created_at', [$start, $end])
            ->where('product.type', $type);

        $drinkRows = $itemQuery('drink')
            ->select('product.id', 'product.name')
            ->selectRaw('SUM(item.quantity) AS quantity, SUM(item.total_price) AS amount')
            ->groupBy('product.id', 'product.name')
            ->orderByDesc('quantity')->get();
        $extraRows = $itemQuery('wash')
            ->where('item.is_primary_wash', false)
            ->select('product.id', 'product.name')
            ->selectRaw('SUM(item.quantity) AS quantity, SUM(item.total_price) AS amount')
            ->groupBy('product.id', 'product.name')
            ->orderByDesc('quantity')->get();

        // Cruzamento por serviço e categoria de veículo. A lavagem principal é
        // contada uma única vez por OS; lavagens extras ficam em outro conjunto.
        // Os valores são vendas dos itens registrados no dia, NÃO recebimentos.
        $washByVehicle = static function (bool $primary) use ($itemQuery): array {
            $rows = $itemQuery('wash')->where('so.order_type', 'service')
                ->where('item.is_primary_wash', $primary)
                ->select('product.id', 'product.name', 'so.vehicle_type')
                ->selectRaw('SUM(item.quantity) AS quantity, SUM(item.total_price) AS amount')
                ->groupBy('product.id', 'product.name', 'so.vehicle_type')
                ->orderBy('product.name')
                ->get();

            $groups = [];
            foreach ($rows as $row) {
                if (!in_array($row->vehicle_type, ['normal', 'suv', 'pickup'], true)) continue;
                $id = (int) $row->id;
                if (!isset($groups[$id])) {
                    $groups[$id] = [
                        'id' => $id,
                        'name' => $row->name,
                        'quantity' => 0,
                        'amount' => 0.0,
                        'vehicles' => ['normal' => 0, 'suv' => 0, 'pickup' => 0],
                        'vehicle_amounts' => ['normal' => 0.0, 'suv' => 0.0, 'pickup' => 0.0],
                    ];
                }
                $qty = (int) $row->quantity;
                $amount = (float) $row->amount;
                $groups[$id]['quantity'] += $qty;
                $groups[$id]['amount'] = round($groups[$id]['amount'] + $amount, 2);
                $groups[$id]['vehicles'][$row->vehicle_type] = $qty;
                $groups[$id]['vehicle_amounts'][$row->vehicle_type] = round($amount, 2);
            }

            $items = array_values($groups);
            usort($items, static fn ($a, $b) => $b['quantity'] <=> $a['quantity'] ?: strcasecmp($a['name'], $b['name']));
            return [
                'quantity' => array_sum(array_column($items, 'quantity')),
                'amount' => round(array_sum(array_column($items, 'amount')), 2),
                'items' => $items,
            ];
        };

        $map = static fn ($rows) => [
            'quantity' => (int) $rows->sum('quantity'),
            'amount' => round((float) $rows->sum('amount'), 2),
            'items' => $rows->map(fn ($row) => [
                'name' => $row->name,
                'quantity' => (int) $row->quantity,
                'amount' => round((float) $row->amount, 2),
            ])->take(5)->values()->all(),
        ];

        return [
            'date' => $day->format('d/m/Y'),
            'date_iso' => $day->format('Y-m-d'),
            'orders' => array_sum($vehicles),
            'sales' => ['orders' => (int) $saleSummary->quantity, 'amount' => round((float) $saleSummary->amount, 2)],
            'vehicles' => $vehicles,
            'received' => round($receivedTotal, 2),
            'payments' => $payments,
            'drinks' => $map($drinkRows),
            'wash_addons' => $map($extraRows),
            'wash_types' => $washByVehicle(true),
            'wash_addon_types' => $washByVehicle(false),
        ];
    }
}
