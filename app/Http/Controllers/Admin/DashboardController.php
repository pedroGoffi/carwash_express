<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Models\ServiceOrder;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalCompanies = Company::count();
        $totalUsers = User::count();
        $totalOrders = ServiceOrder::count();
        $totalRevenue = Payment::sum('amount');
        
        $recentOrders = ServiceOrder::with('company')
            ->latest()
            ->take(10)
            ->get();
        
        return view('admin.dashboard', compact(
            'totalCompanies',
            'totalUsers', 
            'totalOrders',
            'totalRevenue',
            'recentOrders'
        ));
    }
}