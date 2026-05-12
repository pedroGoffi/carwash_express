<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\ServiceOrder;
use App\Models\Product;
use App\Enums\ServiceOrderStatus;

class OperatorDashboardController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id;

        $queued = ServiceOrder::where('company_id', $companyId)
            ->where('status', ServiceOrderStatus::QUEUED)
            ->orderBy('created_at', 'asc')
            ->get();

        $finished = ServiceOrder::where('company_id', $companyId)
            ->where('status', ServiceOrderStatus::FINISHED)
            ->whereDate('finished_at', today())
            ->latest()
            ->get();

        $products = Product::where('company_id', $companyId)
            ->where('active', true)
            ->get();

        return view('operator.dashboard', compact('queued', 'finished', 'products'));
    }
}