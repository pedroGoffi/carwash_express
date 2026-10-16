<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Manager\AdvanceController;
use App\Http\Controllers\Manager\EmployeeExpenseController;
use App\Http\Controllers\Manager\OperatingExpenseController;
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



Route::get('/health', fn () => response()->json(['status' => 'ok']))->name('health');

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});
require __DIR__.'/auth.php';

// ========== ROTAS DO ADMIN ==========
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
});

// ========== ROTAS DO GERENTE ==========
Route::middleware(['auth', 'role:admin,manager'])->prefix('manager')->name('manager.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [ManagerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/analytics', [ManagerDashboardController::class, 'analytics'])->name('dashboard.analytics');
    // Produtos (CRUD completo)
    Route::resource('products', ProductController::class)->except(['show']);
    
    // Usuários (CRUD completo)
    Route::resource('users', UserController::class)->except(['show']);
    
    // Relatórios
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/data', [ReportController::class, 'getData'])->name('reports.data');

    // Custos operacionais, separados da folha de pagamento.
    Route::get('/operating-expenses', [OperatingExpenseController::class, 'index'])->name('operating-expenses.index');
    Route::post('/operating-expenses', [OperatingExpenseController::class, 'store'])->name('operating-expenses.store');
    Route::delete('/operating-expenses/{expense}', [OperatingExpenseController::class, 'destroy'])->name('operating-expenses.destroy');

    // Adiantamentos
    Route::resource('advances', AdvanceController::class)->except(['show', 'create', 'edit', 'update']);

    // Consumos
    Route::resource('consumptions', ConsumptionController::class)->except(['show', 'create', 'edit', 'update']);
    
    // Folha de Pagamento
    Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
    // Remuneracao é editada apenas no cadastro do colaborador.
    Route::post('/expenses', [EmployeeExpenseController::class, 'store'])->name('expenses.store');
    Route::delete('/expenses/{expense}', [EmployeeExpenseController::class, 'destroy'])->name('expenses.destroy');
    Route::post('/payroll/close-period', [PayrollController::class, 'closePeriod'])->name('payroll.close-period');

    
    // Anotações
    Route::get('/notes', [ManagerNoteController::class, 'index'])->name('notes.index');
    Route::delete('/notes/{note}', [ManagerNoteController::class, 'destroy'])->name('notes.destroy');
    Route::post('/notes/{note}/pin', [ManagerNoteController::class, 'togglePin'])->name('notes.pin');
});

// ========== ROTAS DO OPERADOR ==========
Route::middleware(['auth', 'role:admin,manager,operator'])->prefix('operator')->name('operator.')->group(function () {    
    // Alias para clientes antigos ou redirecionamentos pós-login.
    Route::get('/dashboard', fn () => redirect()->route('operator.orders.index'))->name('dashboard');

    // Ordens de Serviço
    Route::get('/orders', [ServiceOrderController::class, 'index'])->name('orders.index');
    Route::get('/create-order', [ServiceOrderController::class, 'create'])->name('orders.create');
    Route::get('/orders/today-summary', [ServiceOrderController::class, 'todaySummary'])->name('orders.today-summary');
    Route::get('/daily-summary', [ServiceOrderController::class, 'dailySummary'])->name('daily-summary');
    Route::post('/orders', [ServiceOrderController::class, 'store'])->name('orders.store');
    
    // Recibo, finalizar e Cancelar
        
    
    Route::get('/orders/{order}/receipt', [ServiceOrderController::class, 'receipt'])->name('orders.receipt');
    Route::post('/orders/{order}/finish', [ServiceOrderController::class, 'finish'])->name('orders.finish');
    Route::post('/orders/{order}/status', [ServiceOrderController::class, 'updateStatus'])->name('orders.status');
    Route::post('/orders/{order}/payments', [ServiceOrderController::class, 'addPayment'])->name('orders.payments.store');
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
    
    abort(403, 'Perfil de usuário sem painel configurado.');
})->name('dashboard');