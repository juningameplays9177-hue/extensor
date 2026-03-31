<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ContainerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepotController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FinancePanel3Controller;
use App\Http\Controllers\OldClientController;
use App\Http\Controllers\ReceivableController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/', fn () => redirect()->route('login'));
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('depots', DepotController::class)->except(['create', 'show', 'edit']);
    Route::resource('containers', ContainerController::class)->except(['create', 'show', 'edit']);
    Route::resource('clients', ClientController::class)->except(['show']);

    Route::get('/rentals', [RentalController::class, 'index'])->name('rentals.index');
    Route::post('/rentals', [RentalController::class, 'store'])->name('rentals.store');
    Route::patch('/rentals/{rental}/close', [RentalController::class, 'close'])->name('rentals.close');
    Route::patch('/rentals/{rental}/toggle-service-done', [RentalController::class, 'toggleServiceDone'])->name('rentals.toggle-service-done');

    // Rotas financeiras (apenas administradores)
    Route::middleware('admin')->group(function (): void {
        Route::resource('users', UserController::class)->except(['show']);
        
        Route::resource('expenses', ExpenseController::class)->except(['show']);
        Route::patch('/expenses/{expense}/mark-as-paid', [ExpenseController::class, 'markAsPaid'])->name('expenses.mark-as-paid');
        Route::post('/expenses/saldo-gastos-dia', [ExpenseController::class, 'storeSaldoGastoDia'])->name('expenses.saldo-gastos.store');
        Route::put('/expenses/saldo-gastos-dia/{daily_saldo_gasto_item}', [ExpenseController::class, 'updateSaldoGastoDia'])->name('expenses.saldo-gastos.update');
        Route::delete('/expenses/saldo-gastos-dia/{daily_saldo_gasto_item}', [ExpenseController::class, 'destroySaldoGastoDia'])->name('expenses.saldo-gastos.destroy');
        Route::get('/old-clients', [OldClientController::class, 'index'])->name('old-clients.index');
        Route::post('/old-clients', [OldClientController::class, 'store'])->name('old-clients.store');
        Route::patch('/old-clients/{old_client}/toggle-checked', [OldClientController::class, 'toggleChecked'])->name('old-clients.toggle-checked');
        Route::delete('/old-clients/{old_client}', [OldClientController::class, 'destroy'])->name('old-clients.destroy');
        Route::get('/finance/panel-3', [FinancePanel3Controller::class, 'index'])->name('finance.panel-3');
        Route::post('/finance/panel-3/people-who-owe', [FinancePanel3Controller::class, 'storePersonWhoOwes'])->name('finance.panel-3.people-who-owe.store');
        Route::put('/finance/panel-3/people-who-owe/{receivable}', [FinancePanel3Controller::class, 'updatePersonWhoOwes'])->name('finance.panel-3.people-who-owe.update');
        
        Route::resource('receivables', ReceivableController::class)->except(['show']);
        Route::patch('/receivables/{receivable}/mark-as-paid', [ReceivableController::class, 'markAsPaid'])->name('receivables.mark-as-paid');
        
        Route::get('/rentals/{rental}/edit', [RentalController::class, 'edit'])->name('rentals.edit');
        Route::put('/rentals/{rental}', [RentalController::class, 'update'])->name('rentals.update');
    });
});
