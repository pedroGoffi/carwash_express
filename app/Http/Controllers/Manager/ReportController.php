<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\ServiceOrder;
use App\Models\Payment;
use App\Models\Product;
use App\Enums\ServiceOrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        return view('manager.reports.index');
    }

    public function getData(Request $request)
    {
        $companyId = auth()->user()->company_id;
        
        // Definir período
        $period = $request->get('period');
        $startDate = null;
        $endDate = null;
        
        switch ($period) {
            case 'today':
                $startDate = now()->startOfDay();
                $endDate = now()->endOfDay();
                break;
            case 'yesterday':
                $startDate = now()->subDay()->startOfDay();
                $endDate = now()->subDay()->endOfDay();
                break;
            case 'week':
                $startDate = now()->startOfWeek();
                $endDate = now()->endOfWeek();
                break;
            case 'month':
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
                break;
            case 'last6months':
                $startDate = now()->subMonths(6)->startOfDay();
                $endDate = now()->endOfDay();
                break;
            default:
                $startDate = $request->get('start') ? now()->parse($request->get('start'))->startOfDay() : now()->subDays(30)->startOfDay();
                $endDate = $request->get('end') ? now()->parse($request->get('end'))->endOfDay() : now()->endOfDay();
                break;
        }
        
        // Totais
        $totalOrders = ServiceOrder::where('company_id', $companyId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
            
        $totalRevenue = Payment::whereHas('serviceOrder', function($q) use ($companyId, $startDate, $endDate) {
            $q->where('company_id', $companyId)->whereBetween('created_at', [$startDate, $endDate]);
        })->sum('amount');
        
        $averageTicket = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        
        $totalCustomers = ServiceOrder::where('company_id', $companyId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('customer_name')
            ->distinct('customer_name')
            ->count('customer_name');
        
        // Dados diários para gráficos
        $dailyData = Payment::whereHas('serviceOrder', function($q) use ($companyId, $startDate, $endDate) {
            $q->where('company_id', $companyId)->whereBetween('created_at', [$startDate, $endDate]);
        })
        ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as revenue'), DB::raw('COUNT(*) as orders'))
        ->groupBy('date')
        ->orderBy('date', 'asc')
        ->get();
        
        // Top produtos
        $topProducts = DB::table('service_order_items')
            ->join('service_orders', 'service_order_items.service_order_id', '=', 'service_orders.id')
            ->join('products', 'service_order_items.product_id', '=', 'products.id')
            ->where('service_orders.company_id', $companyId)
            ->whereBetween('service_orders.created_at', [$startDate, $endDate])
            ->select('products.name', DB::raw('SUM(service_order_items.quantity) as total'))
            ->groupBy('products.id', 'products.name')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();
        
        $maxTotal = $topProducts->first()->total ?? 1;
        foreach ($topProducts as $product) {
            $product->percent = ($product->total / $maxTotal) * 100;
        }
        
        // Pagamentos por método
        $paymentsByMethod = Payment::whereHas('serviceOrder', function($q) use ($companyId, $startDate, $endDate) {
            $q->where('company_id', $companyId)->whereBetween('created_at', [$startDate, $endDate]);
        })
        ->select('method', DB::raw('SUM(amount) as total'))
        ->groupBy('method')
        ->get();
        
        // Tabela detalhada: uma única agregação, sem uma query adicional por dia.
        $ordersByDay = ServiceOrder::where('company_id', $companyId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupByRaw('DATE(created_at)')
            ->pluck('total', 'date');

        $detailedData = [];
        foreach ($dailyData as $day) {
            $dayOrders = (int) ($ordersByDay[$day->date] ?? 0);
            $detailedData[] = [
                'date' => date('d/m/Y', strtotime($day->date)),
                'orders' => $dayOrders,
                'revenue' => number_format($day->revenue, 2, ',', '.'),
                'avgTicket' => number_format($dayOrders > 0 ? $day->revenue / $dayOrders : 0, 2, ',', '.')
            ];
        }
        
        return response()->json([
            'totalOrders' => $totalOrders,
            'totalRevenue' => $totalRevenue,
            'averageTicket' => $averageTicket,
            'totalCustomers' => $totalCustomers,
            'revenueLabels' => $dailyData->pluck('date'),
            'revenueData' => $dailyData->pluck('revenue'),
            'ordersLabels' => $dailyData->pluck('date'),
            'ordersData' => $dailyData->pluck('orders'),
            'paymentLabels' => $paymentsByMethod->pluck('method'),
            'paymentData' => $paymentsByMethod->pluck('total'),
            'topProducts' => $topProducts,
            'dailyData' => $detailedData
        ]);
    }
}