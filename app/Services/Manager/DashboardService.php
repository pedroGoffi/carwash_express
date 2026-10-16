<?php

namespace App\Services\Manager;

use App\Enums\ServiceOrderStatus;
use App\Support\ResilientCache;
use Illuminate\Support\Facades\DB;

final class DashboardService
{
    public function core(int $companyId): array
    {
        return ResilientCache::remember("manager-dashboard:core:{$companyId}", 15, function () use ($companyId) {
            $now = now();
            $dayStart = $now->copy()->startOfDay();
            $dayEnd = $now->copy()->endOfDay();
            $monthStart = $now->copy()->startOfMonth();
            $monthEnd = $now->copy()->endOfMonth();

            $orderStats = DB::table('service_orders')
                ->where('company_id', $companyId)
                ->selectRaw('COUNT(*) FILTER (WHERE order_type = \'service\' AND created_at BETWEEN ? AND ?) AS today_count', [$dayStart, $dayEnd])
                ->selectRaw('COUNT(*) FILTER (WHERE order_type = \'service\' AND created_at BETWEEN ? AND ?) AS month_count', [$monthStart, $monthEnd])
                ->selectRaw('COUNT(*) FILTER (WHERE order_type = \'sale\' AND created_at BETWEEN ? AND ?) AS today_sale_count', [$dayStart, $dayEnd])
                ->selectRaw('COUNT(*) FILTER (WHERE order_type = \'sale\' AND created_at BETWEEN ? AND ?) AS month_sale_count', [$monthStart, $monthEnd])
                ->selectRaw('COUNT(*) FILTER (WHERE status = ?) AS waiting_count', [ServiceOrderStatus::WAITING->value])
                ->selectRaw('COUNT(*) FILTER (WHERE status = ?) AS queue_count', [ServiceOrderStatus::QUEUED->value])
                ->selectRaw('COUNT(*) FILTER (WHERE status = ?) AS ready_count', [ServiceOrderStatus::FINISHED->value])
                ->first();

            $revenue = DB::table('payments')
                ->join('service_orders', 'payments.service_order_id', '=', 'service_orders.id')
                ->where('service_orders.company_id', $companyId)
                ->selectRaw('COALESCE(SUM(payments.amount) FILTER (WHERE payments.created_at BETWEEN ? AND ?), 0) AS today_revenue', [$dayStart, $dayEnd])
                ->selectRaw('COALESCE(SUM(payments.amount) FILTER (WHERE payments.created_at BETWEEN ? AND ?), 0) AS month_revenue', [$monthStart, $monthEnd])
                ->first();

            $recentOrders = DB::table('service_orders')
                ->where('company_id', $companyId)
                ->latest('id')
                ->limit(8)
                ->get(['id', 'code', 'customer_name', 'license_plate', 'order_type', 'status', 'total', 'created_at'])
                ->map(fn ($order) => [
                    'id' => (int) $order->id,
                    'code' => $order->code,
                    'customer_name' => $order->customer_name,
                    'license_plate' => $order->license_plate,
                    'order_type' => $order->order_type,
                    'status' => $order->status,
                    'total' => (float) $order->total,
                    'created_at' => $order->created_at,
                ])->all();

            $todayOrders = (int) ($orderStats->today_count ?? 0);
            $todayRevenue = (float) ($revenue->today_revenue ?? 0);
            $todaySales = (int) ($orderStats->today_sale_count ?? 0);

            return [
                'totalOrdersToday' => $todayOrders,
                'totalOrdersMonth' => (int) ($orderStats->month_count ?? 0),
                'salesToday' => $todaySales,
                'salesMonth' => (int) ($orderStats->month_sale_count ?? 0),
                'totalRevenueToday' => $todayRevenue,
                'totalRevenueMonth' => (float) ($revenue->month_revenue ?? 0),
                'averageTicketToday' => ($todayOrders + $todaySales) > 0 ? $todayRevenue / ($todayOrders + $todaySales) : 0,
                'waitingCount' => (int) ($orderStats->waiting_count ?? 0),
                'queueCount' => (int) ($orderStats->queue_count ?? 0),
                'readyCount' => (int) ($orderStats->ready_count ?? 0),
                'recentOrders' => $recentOrders,
            ];
        });
    }

    public function analytics(int $companyId): array
    {
        return ResilientCache::remember("manager-dashboard:analytics:{$companyId}", 60, function () use ($companyId) {
            $now = now();
            $monthStart = $now->copy()->startOfMonth();
            $monthEnd = $now->copy()->endOfMonth();
            $sixMonthStart = $now->copy()->subMonths(5)->startOfMonth();

            // Salário-base mensal ou valor de diária × dias; nunca soma os dois.
            $totalSalaries = (float) DB::table('users')
                ->where('company_id', $companyId)->where('role','operator')->where('active',true)
                ->selectRaw('COALESCE(SUM(CASE WHEN COALESCE(daily_rate,0) > 0 THEN daily_rate * COALESCE(days_worked,0) ELSE COALESCE(monthly_salary,0) END),0) AS total')
                ->value('total');
            $extraCosts = (float) DB::table('employee_expenses')
                ->where('company_id', $companyId)
                ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->sum('amount');

            $operatingCosts = (float) DB::table('operating_expenses')
                ->where('company_id', $companyId)
                ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->sum('amount');

            $costs = DB::table('advances')
                ->where('company_id', $companyId)
                ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->selectRaw("COALESCE(SUM(amount) FILTER (WHERE type = 'money'), 0) AS advances")
                ->selectRaw("COALESCE(SUM(amount) FILTER (WHERE type = 'product'), 0) AS consumptions")
                ->first();

            $monthRevenue = (float) DB::table('payments')
                ->join('service_orders', 'payments.service_order_id', '=', 'service_orders.id')
                ->where('service_orders.company_id', $companyId)
                ->whereBetween('payments.created_at', [$monthStart, $monthEnd])
                ->sum('payments.amount');

            $revenueMonths = DB::table('payments')
                ->join('service_orders', 'payments.service_order_id', '=', 'service_orders.id')
                ->where('service_orders.company_id', $companyId)
                ->whereBetween('payments.created_at', [$sixMonthStart, $monthEnd])
                ->selectRaw("DATE_TRUNC('month', payments.created_at) AS month, SUM(payments.amount) AS total")
                ->groupByRaw("DATE_TRUNC('month', payments.created_at)")
                ->get()
                ->keyBy(fn ($row) => substr((string) $row->month, 0, 7));

            $expenseMonths = DB::table('employee_expenses')
                ->where('company_id', $companyId)
                ->whereBetween('date', [$sixMonthStart->toDateString(), $monthEnd->toDateString()])
                ->selectRaw("DATE_TRUNC('month', date) AS month, SUM(amount) AS total")
                ->groupByRaw("DATE_TRUNC('month', date)")
                ->get()
                ->keyBy(fn ($row) => substr((string) $row->month, 0, 7));

            $operatingMonths = DB::table('operating_expenses')
                ->where('company_id', $companyId)
                ->whereBetween('date', [$sixMonthStart->toDateString(), $monthEnd->toDateString()])
                ->selectRaw("DATE_TRUNC('month', date) AS month, SUM(amount) AS total")
                ->groupByRaw("DATE_TRUNC('month', date)")
                ->get()->keyBy(fn ($row) => substr((string) $row->month, 0, 7));

            $months = collect(range(5, 0))->map(function ($i) use ($now, $revenueMonths, $expenseMonths, $operatingMonths, $totalSalaries) {
                $month = $now->copy()->subMonths($i)->startOfMonth();
                $key = $month->format('Y-m');
                $revenue = (float) ($revenueMonths->get($key)->total ?? 0);
                $expenses = $totalSalaries + (float) ($expenseMonths->get($key)->total ?? 0)
                    + (float) ($operatingMonths->get($key)->total ?? 0);

                return [
                    'label' => $month->translatedFormat('M/y'),
                    'revenue' => $revenue,
                    'expenses' => $expenses,
                    'profit' => $revenue - $expenses,
                ];
            })->values()->all();

            $topProducts = DB::table('service_order_items')
                ->join('service_orders', 'service_order_items.service_order_id', '=', 'service_orders.id')
                ->join('products', 'service_order_items.product_id', '=', 'products.id')
                ->where('service_orders.company_id', $companyId)
                ->groupBy('products.id', 'products.name')
                ->orderByRaw('SUM(service_order_items.quantity) DESC')
                ->limit(5)
                ->get(['products.name', DB::raw('SUM(service_order_items.quantity) AS total')])
                ->map(fn ($row) => ['name' => $row->name, 'total' => (int) $row->total])
                ->all();

            $advances = (float) ($costs->advances ?? 0);
            $consumptions = (float) ($costs->consumptions ?? 0);
            $expenses = $totalSalaries + $extraCosts + $operatingCosts;
            $profit = $monthRevenue - $expenses;

            return [
                'monthRevenue' => $monthRevenue,
                'salaries' => $totalSalaries,
                'advances' => $advances,
                'consumptions' => $consumptions,
                'extraCosts' => $extraCosts,
                'operatingCosts' => $operatingCosts,
                'expenses' => $expenses,
                'profit' => $profit,
                'margin' => $monthRevenue > 0 ? ($profit / $monthRevenue) * 100 : 0,
                'months' => $months,
                'topProducts' => $topProducts,
            ];
        });
    }
}
