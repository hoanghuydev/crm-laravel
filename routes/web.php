<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CustomerController;
use App\Http\Controllers\Web\CustomerTypeController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\PaymentMethodController;
use App\Http\Controllers\Web\OrderController;
use App\Http\Controllers\Web\DiscountController;

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard/Home
Route::get('/', function () {
    return redirect()->route('dashboard');
});
Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard')->middleware('auth');

// Customer Types Management
Route::resource('customer-types', CustomerTypeController::class);
Route::post('customer-types/recalculate-scores', [CustomerTypeController::class, 'recalculateScores'])->name('customer-types.recalculate-scores');
Route::get('customer-types/default-weights', [CustomerTypeController::class, 'getDefaultWeights'])->name('customer-types.default-weights');
Route::get('customer-types/cron/recalculate-scores', [CustomerTypeController::class, 'cronRecalculateScores'])->name('customer-types.cron.recalculate-scores');

// Customer Management
Route::resource('customers', CustomerController::class);
Route::patch('customers/{customer}/activate', [CustomerController::class, 'activate'])->name('customers.activate');

// Products Management
Route::resource('products', ProductController::class);
Route::get('products/reports/low-stock', [ProductController::class, 'lowStock'])->name('products.low-stock');

// Payment Methods Management
Route::resource('payment-methods', PaymentMethodController::class);
Route::patch('payment-methods/{paymentMethod}/toggle-status', [PaymentMethodController::class, 'toggleStatus'])->name('payment-methods.toggle-status');

// Orders Management
Route::resource('orders', OrderController::class);
Route::post('orders/preview-calculation', [OrderController::class, 'previewCalculation'])->name('orders.preview-calculation');
Route::patch('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
Route::patch('orders/{order}/update-status', [OrderController::class, 'updateStatus'])->name('orders.update-status');

// Discounts Management
Route::resource('discounts', DiscountController::class);
Route::patch('discounts/{discount}/toggle-status', [DiscountController::class, 'toggleStatus'])->name('discounts.toggle-status');
