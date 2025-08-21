<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerTypeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DiscountController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// API Routes for E-commerce System
Route::prefix('v1')->name('api.')->group(function () {
    
    // Customer Type routes
    Route::apiResource('customer-types', CustomerTypeController::class);
    Route::patch('customer-types/{id}/activate', [CustomerTypeController::class, 'activate'])->name('customer-types.activate');
    Route::delete('customer-types/{id}/force', [CustomerTypeController::class, 'forceDelete'])->name('customer-types.force-delete');
    
    // Customer routes
    Route::apiResource('customers', CustomerController::class);
    Route::get('customers/paginated', [CustomerController::class, 'paginated'])->name('customers.paginated');
    Route::get('customers/search', [CustomerController::class, 'search'])->name('customers.search');
    Route::get('customers/{id}/stats', [CustomerController::class, 'stats'])->name('customers.stats');
    Route::get('customers/by-type/{typeId}', [CustomerController::class, 'byType'])->name('customers.by-type');
    Route::patch('customers/{id}/activate', [CustomerController::class, 'activate'])->name('customers.activate');
    Route::delete('customers/{id}/force', [CustomerController::class, 'forceDelete'])->name('customers.force-delete');
    
    // Product routes
    Route::apiResource('products', ProductController::class);
    Route::get('products/search', [ProductController::class, 'search'])->name('products.search');
    Route::get('products/low-stock', [ProductController::class, 'lowStock'])->name('products.low-stock');
    
    // Order routes
    Route::apiResource('orders', OrderController::class);
    Route::patch('orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::patch('orders/{id}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('orders/revenue/report', [OrderController::class, 'revenue'])->name('orders.revenue');
    
    // Discount routes
    Route::apiResource('discounts', DiscountController::class);
    Route::post('discounts/validate', [DiscountController::class, 'validateDiscount'])->name('discounts.validate');
    Route::get('discounts/applicable', [DiscountController::class, 'getApplicable'])->name('discounts.applicable');
    
});
