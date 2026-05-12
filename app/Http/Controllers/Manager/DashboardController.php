<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\ServiceOrder;
use App\Models\Product;
use App\Models\Payment;
use App\Models\User;
use App\Models\Advance;
use App\Enums\ServiceOrderStatus;
use App\Enums\ProductType;
use Illuminate\Support\Facades\DB;
use Request;

class DashboardController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id;
        
        // Métricas do dia
        $totalOrdersToday = ServiceOrder::where('company_id', $companyId)
            ->whereDate('created_at', today())
            ->count();
            
        $totalRevenueToday = Payment::whereHas('serviceOrder', function($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->whereDate('created_at', today())->sum('amount');
        
        $queueCount = ServiceOrder::where('company_id', $companyId)
            ->where('status', ServiceOrderStatus::QUEUED)
            ->count();
            
        $averageTicket = ServiceOrder::where('company_id', $companyId)
            ->where('status', ServiceOrderStatus::FINISHED)
            ->avg('total') ?? 0;
        
        // ========== GASTOS COM COLABORADORES ==========
        $totalSalaries = User::where('company_id', $companyId)
            ->where('role', 'operator')
            ->sum('monthly_salary');
        
        $totalAdvances = Advance::where('company_id', $companyId)
            ->where('type', 'money')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');
        
        $totalConsumptions = Advance::where('company_id', $companyId)
            ->where('type', 'product')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');
        
        $totalExpenses = $totalSalaries + $totalAdvances + $totalConsumptions;
        
        // ========== FATURAMENTO MÊS ==========
        $totalRevenueMonth = Payment::whereHas('serviceOrder', function($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->sum('amount');
        
        $totalOrdersMonth = ServiceOrder::where('company_id', $companyId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        $averageTicketMonth = $totalOrdersMonth > 0 ? $totalRevenueMonth / $totalOrdersMonth : 0;
        
        $totalProfit = $totalRevenueMonth - $totalExpenses;
        $profitMargin = $totalRevenueMonth > 0 ? ($totalProfit / $totalRevenueMonth) * 100 : 0;
        
        // ========== DADOS PARA GRÁFICO (Últimos 6 meses) ==========
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthName = $month->format('M/Y');
            
            $revenue = Payment::whereHas('serviceOrder', function($q) use ($companyId, $month) {
                $q->where('company_id', $companyId)
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year);
            })->sum('amount');
            
            $salaries = User::where('company_id', $companyId)
                ->where('role', 'operator')
                ->sum('monthly_salary');
            
            $advances = Advance::where('company_id', $companyId)
                ->where('type', 'money')
                ->whereMonth('date', $month->month)
                ->whereYear('date', $month->year)
                ->sum('amount');
            
            $consumptions = Advance::where('company_id', $companyId)
                ->where('type', 'product')
                ->whereMonth('date', $month->month)
                ->whereYear('date', $month->year)
                ->sum('amount');
            
            $expenses = $salaries + $advances + $consumptions;
            $profit = $revenue - $expenses;
            
            $monthlyData[] = [
                'month' => $monthName,
                'revenue' => $revenue,
                'expenses' => $expenses,
                'profit' => $profit,
            ];
        }
        
        $chartMonths = collect($monthlyData)->pluck('month');
        $chartRevenue = collect($monthlyData)->pluck('revenue');
        $chartExpenses = collect($monthlyData)->pluck('expenses');
        $chartProfit = collect($monthlyData)->pluck('profit');
        
        // Top produtos
        $topProducts = DB::table('service_order_items')
            ->join('service_orders', 'service_order_items.service_order_id', '=', 'service_orders.id')
            ->join('products', 'service_order_items.product_id', '=', 'products.id')
            ->where('service_orders.company_id', $companyId)
            ->select('products.name', DB::raw('SUM(service_order_items.quantity) as total'))
            ->groupBy('products.id', 'products.name')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();
        
        // Últimas ordens
        $recentOrders = ServiceOrder::where('company_id', $companyId)
            ->latest()
            ->take(10)
            ->get();
        
        // Faturamento por dia (últimos 30 dias)
        $revenueByDay = Payment::whereHas('serviceOrder', function($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })
        ->whereDate('created_at', '>=', now()->subDays(30))
        ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as total'))
        ->groupBy('date')
        ->orderBy('date', 'asc')
        ->get();
        
        // Clientes
        $totalCustomers = ServiceOrder::where('company_id', $companyId)
            ->whereNotNull('customer_name')
            ->distinct('customer_name')
            ->count('customer_name');
        
        $totalCorporateCustomers = ServiceOrder::where('company_id', $companyId)
            ->where('customer_type', 'pj')
            ->distinct('customer_document')
            ->count('customer_document');
        
        // Produtos
        $totalProducts = Product::where('company_id', $companyId)
            ->where('active', true)
            ->count();
        
        $totalWashes = Product::where('company_id', $companyId)
            ->where('type', ProductType::WASH)
            ->where('active', true)
            ->count();
        
        return view('manager.dashboard', compact(
            'totalOrdersToday',
            'totalRevenueToday',
            'queueCount',
            'averageTicket',
            'totalExpenses',
            'totalRevenueMonth',
            'totalOrdersMonth',
            'averageTicketMonth',
            'totalProfit',
            'profitMargin',
            'chartMonths',
            'chartRevenue',
            'chartExpenses',
            'chartProfit',
            'topProducts',
            'recentOrders',
            'revenueByDay',
            'totalCustomers',
            'totalCorporateCustomers',
            'totalProducts',
            'totalWashes',
            'totalSalaries',
            'totalAdvances',
            'totalConsumptions'
        ));
    }


    public function chartData(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $period = $request->get('period', 'week');
        
        if ($period == 'week') {
            $startDate = now()->startOfWeek();
            $endDate = now()->endOfWeek();
        } else {
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();
        }
        
        $data = Payment::whereHas('serviceOrder', function($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })
        ->whereBetween('created_at', [$startDate, $endDate])
        ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as total'))
        ->groupBy('date')
        ->orderBy('date', 'asc')
        ->get();
        
        return response()->json([
            'labels' => $data->pluck('date'),
            'values' => $data->pluck('total')
        ]);
    }
}