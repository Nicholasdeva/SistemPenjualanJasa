<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Buyer;
use App\Http\Controllers\Seller;
use App\Http\Controllers\Shared\WorkspaceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\PasswordResetController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);

    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);

    Route::get('forgot-password', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
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
       
        Route::get('/', fn () => redirect()->route('admin.dashboard'))->name('home');
        Route::get('dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

        Route::get('sellers', [Admin\SellerController::class, 'index'])->name('sellers.index');
        Route::patch('sellers/{user}/verify', [Admin\SellerController::class, 'verify'])->name('sellers.verify');
        Route::patch('sellers/{user}/unverify', [Admin\SellerController::class, 'unverify'])->name('sellers.unverify');

        Route::get('categories', [Admin\CategoryController::class, 'index'])->name('categories.index');
        Route::post('categories', [Admin\CategoryController::class, 'store'])->name('categories.store');
        Route::put('categories/{category}', [Admin\CategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [Admin\CategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('services', [Admin\ServiceController::class, 'index'])->name('services.index');
        Route::patch('services/{service}/activate', [Admin\ServiceController::class, 'activate'])->name('services.activate');
        Route::patch('services/{service}/deactivate', [Admin\ServiceController::class, 'deactivate'])->name('services.deactivate');

        Route::get('withdrawals', [Admin\WithdrawalController::class, 'index'])->name('withdrawals.index');
        Route::patch('withdrawals/{withdrawal}/process', [Admin\WithdrawalController::class, 'process'])->name('withdrawals.process');

        Route::get('disputes', [Admin\DisputeController::class, 'index'])->name('disputes.index');
        Route::get('disputes/{dispute}', [Admin\DisputeController::class, 'show'])->name('disputes.show');
        Route::patch('disputes/{dispute}/resolve', [Admin\DisputeController::class, 'resolve'])->name('disputes.resolve');
    });