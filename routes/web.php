<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Manager\AdvanceController;
use App\Http\Controllers\Manager\ConsumptionController;
use App\Http\Controllers\Manager\DashboardController as ManagerDashboardController;
use App\Http\Controllers\Manager\NoteController;
use App\Http\Controllers\Manager\PayrollController;
use App\Http\Controllers\Manager\ProductController;
use App\Http\Controllers\Manager\UserController;
use App\Http\Controllers\Manager\ReportController;
use App\Http\Controllers\Manager\NoteController as ManagerNoteController;
use App\Http\Controllers\Operator\OperatorDashboardController;
use App\Http\Controllers\Operator\ServiceOrderController;
use App\Http\Controllers\Operator\NoteController as OperatorNoteController;
use App\Http\Controllers\PrinterController;
use Illuminate\Support\Facades\Route;



Route::get('/', function () {
    return redirect()->route('login');
});
require __DIR__.'/auth.php';

// ========== ROTAS DO ADMIN ==========
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
});

// ========== ROTAS DO GERENTE ==========
Route::middleware(['auth'])->prefix('manager')->name('manager.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [ManagerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart-data', [ManagerDashboardController::class, 'chartData'])->name('manager.dashboard.chart');
    // Produtos (CRUD completo)
    Route::resource('products', ProductController::class);
    
    // Usuários (CRUD completo)
    Route::resource('users', UserController::class);
    
    // Relatórios
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/data', [ReportController::class, 'getData'])->name('reports.data');

    // Adiantamentos
    Route::resource('advances', AdvanceController::class)->except(['show', 'edit', 'update']);

    // Consumos
    Route::resource('consumptions', ConsumptionController::class)->except(['show', 'edit', 'update']);
    
    // Folha de Pagamento
    Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::put('/payroll/{user}', [PayrollController::class, 'update'])->name('payroll.update');

    
    // Anotações
    Route::get('/notes', [ManagerNoteController::class, 'index'])->name('notes.index');
    Route::delete('/notes/{note}', [ManagerNoteController::class, 'destroy'])->name('notes.destroy');
    Route::post('/notes/{note}/pin', [ManagerNoteController::class, 'togglePin'])->name('notes.pin');
});

// ========== ROTAS DO OPERADOR ==========
Route::middleware(['auth'])->prefix('operator')->name('operator.')->group(function () {    
    // Criar Ordem de Serviço
    Route::get('/create-order', [ServiceOrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [ServiceOrderController::class, 'store'])->name('orders.store');
    
    // Recibo, finalizar e Cancelar
        
    
    Route::get('/orders/{order}/receipt', [ServiceOrderController::class, 'receipt'])->name('orders.receipt');
    Route::post('/orders/{order}/finish', [ServiceOrderController::class, 'finish'])->name('orders.finish');
    Route::post('/orders/{order}/request-cancel', [ServiceOrderController::class, 'requestCancel'])->name('orders.request-cancel');

    // Lista de serviços para entregar
    Route::get('/services-to-deliver', [ServiceOrderController::class, 'servicesToDeliver'])->name('services.to-deliver');
    
    // Formulário de entrega
    Route::get('/orders/{order}/deliver', [ServiceOrderController::class, 'showDeliverForm'])->name('orders.deliver-form');
    
    // Finalizar entrega
    Route::post('/orders/{order}/deliver', [ServiceOrderController::class, 'deliver'])->name('orders.deliver');


    
    // Anotações
    Route::get('/notes', [OperatorNoteController::class, 'index'])->name('notes.index');
    Route::post('/notes', [OperatorNoteController::class, 'store'])->name('notes.store');
    Route::delete('/notes/{note}', [OperatorNoteController::class, 'destroy'])->name('notes.destroy');
});

// ========== REDIRECIONAMENTOS POR ROLE ==========
Route::middleware(['auth'])->get('/dashboard', function () {
    $user = auth()->user();
    
    if ($user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    } elseif ($user->isManager()) {
        return redirect()->route('manager.dashboard');
    } elseif ($user->isOperator()) {
        return redirect()->route('operator.orders.create');
    }
    
    return redirect('/');
})->name('dashboard');