<?php

use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\TariffController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\HistoryController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['status' => 'ok']))->name('health');

Route::get('/', [DeliveryController::class, 'create'])->name('home');
Route::get('/delivery', [DeliveryController::class, 'create'])->name('delivery.create');
Route::post('/delivery', [DeliveryController::class, 'store'])->name('delivery.store');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function (): void {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('branches', BranchController::class)->except(['show']);
        Route::resource('tariffs', TariffController::class)->except(['show']);
        Route::resource('users', UserController::class)->except(['show']);
    });
});
