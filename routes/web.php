<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Buyer;
use App\Http\Controllers\Seller;
use App\Http\Controllers\Shared\WorkspaceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
});

Route::post('logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');
Route::get('dashboard', function () {
    $target = auth()->user()->role . '.dashboard';

    abort_unless(Route::has($target), 404, 'Dashboard untuk peran ini belum dibuat.');

    return redirect()->route($target);
})->middleware('auth')->name('dashboard');

Route::middleware(['auth', 'role:buyer'])
    ->prefix('buyer')
    ->name('buyer.')
    ->group(function () {
        Route::get('dashboard', [Buyer\DashboardController::class, 'index'])->name('dashboard');
        Route::get('orders', [Buyer\OrderController::class, 'index'])->name('orders.index');

        Route::get('checkout/{package?}', [Buyer\CheckoutController::class, 'show'])->name('checkout');
        Route::post('checkout', [Buyer\CheckoutController::class, 'store'])->name('checkout.store');

        Route::get('orders/{order?}/review', [Buyer\ReviewController::class, 'create'])->name('reviews.create');
        Route::post('orders/{order?}/review', [Buyer\ReviewController::class, 'store'])->name('reviews.store');
    });

Route::middleware(['auth', 'role:buyer,seller'])
    ->prefix('workspace')
    ->name('workspace.')
    ->group(function () {
        Route::get('{order?}', [WorkspaceController::class, 'show'])->name('show');
        Route::post('{order?}/message', [WorkspaceController::class, 'sendMessage'])->name('message');
        Route::post('{order?}/deliver', [WorkspaceController::class, 'deliverWork'])->name('deliver');
        Route::post('{order?}/revision', [WorkspaceController::class, 'requestRevision'])->name('revision');
        Route::post('{order?}/approve', [WorkspaceController::class, 'approveOrder'])->name('approve');
        Route::post('{order?}/dispute', [WorkspaceController::class, 'openDispute'])->name('dispute');
    });

Route::middleware(['auth', 'role:seller'])
    ->prefix('seller')
    ->name('seller.')
    ->group(function () {
        Route::get('dashboard', [Seller\DashboardController::class, 'index'])->name('dashboard');

        Route::resource('services', Seller\ServiceController::class)->except(['show']);

        Route::get('orders', [Seller\OrderController::class, 'index'])->name('orders.index');

        Route::get('profile', [Seller\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [Seller\ProfileController::class, 'update'])->name('profile.update');

        Route::get('withdrawals', [Seller\WithdrawalController::class, 'index'])->name('withdrawals.index');
        Route::post('withdrawals', [Seller\WithdrawalController::class, 'store'])->name('withdrawals.store');
    });

    Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');
    });
