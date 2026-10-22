<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Seller;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Autentikasi
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store'])->name('register.store');
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
});

Route::post('logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| /dashboard: arahkan pengguna ke dashboard sesuai role-nya
| (buyer.dashboard dan admin.dashboard dibuat oleh Fiki dan Annis)
|--------------------------------------------------------------------------
*/
Route::get('dashboard', function () {
    $target = auth()->user()->role . '.dashboard';

    abort_unless(Route::has($target), 404, 'Dashboard untuk peran ini belum dibuat.');

    return redirect()->route($target);
})->middleware('auth')->name('dashboard');

/*
|--------------------------------------------------------------------------
| Area Penjual
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:seller'])
    ->prefix('seller')
    ->name('seller.')
    ->group(function () {
        Route::get('dashboard', [Seller\DashboardController::class, 'index'])->name('dashboard');

        // services.index, create, store, edit, update, destroy
        Route::resource('services', Seller\ServiceController::class)->except(['show']);

        Route::get('orders', [Seller\OrderController::class, 'index'])->name('orders.index');

        Route::get('profile', [Seller\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [Seller\ProfileController::class, 'update'])->name('profile.update');

        Route::get('withdrawals', [Seller\WithdrawalController::class, 'index'])->name('withdrawals.index');
        Route::post('withdrawals', [Seller\WithdrawalController::class, 'store'])->name('withdrawals.store');
    });