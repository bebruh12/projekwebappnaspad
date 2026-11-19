<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Halaman Publik (Katalog & Detail Lauk)
Route::get('/', [MenuController::class, 'index'])->name('home');
Route::get('/menu/{menu}', [MenuController::class, 'show'])->name('menu.show');

// Redirect otomatis setelah login berdasarkan role
Route::get('/dashboard', function () {
    if (auth()->user()->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('home');
})->middleware(['auth'])->name('dashboard');

// Fitur Pembeli (Wajib Login)
Route::middleware('auth')->group(function () {
    // Ulasan Lauk
    Route::post('/menu/{menu}/review', [MenuController::class, 'storeReview'])->name('review.store');

    // Keranjang Piring
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/{menu}', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/{cart}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cart}', [CartController::class, 'destroy'])->name('cart.destroy');

    // Checkout & Riwayat Pesanan
    Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout.index');
    Route::post('/checkout', [OrderController::class, 'store'])->name('checkout.store');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    // Profil bawaan Breeze
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Fitur Admin
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Kelola Menu Lauk
    Route::get('/menus', [AdminController::class, 'menus'])->name('menus.index');
    Route::get('/menus/create', [AdminController::class, 'createMenu'])->name('menus.create');
    Route::post('/menus', [AdminController::class, 'storeMenu'])->name('menus.store');
    Route::get('/menus/{menu}/edit', [AdminController::class, 'editMenu'])->name('menus.edit');
    Route::put('/menus/{menu}', [AdminController::class, 'updateMenu'])->name('menus.update');
    Route::delete('/menus/{menu}', [AdminController::class, 'destroyMenu'])->name('menus.destroy');

    // Kelola Pesanan & Verifikasi Pembayaran
    Route::get('/orders', [AdminController::class, 'orders'])->name('orders.index');
    Route::patch('/orders/{order}/status', [AdminController::class, 'updateOrderStatus'])->name('orders.status');
    Route::patch('/payments/{payment}/verify', [AdminController::class, 'verifyPayment'])->name('payments.verify');
});

require __DIR__.'/auth.php';