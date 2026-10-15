<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Services\Manager\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

final class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboard)
    {
    }

    public function index(): View
    {
        $companyId = (int) auth()->user()->company_id;

        return view('manager.dashboard', $this->dashboard->core($companyId));
    }

    public function analytics(): JsonResponse
    {
        $companyId = (int) auth()->user()->company_id;

        return response()->json($this->dashboard->analytics($companyId), 200, [
            'Cache-Control' => 'private, max-age=15',
        ]);
    }
}
