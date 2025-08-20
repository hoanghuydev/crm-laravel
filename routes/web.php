<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\CustomerController;
use App\Http\Controllers\Web\CustomerTypeController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\PaymentMethodController;
use App\Http\Controllers\Web\OrderController;

// Dashboard/Home
Route::get('/', function () {
    return redirect()->route('customers.index');
});

// Customer Types Management
Route::resource('customer-types', CustomerTypeController::class);

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
Route::patch('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
Route::patch('orders/{order}/update-status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
