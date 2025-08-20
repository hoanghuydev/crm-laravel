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
Route::prefix('v1')->group(function () {
    
    // Customer Type routes
    Route::apiResource('customer-types', CustomerTypeController::class);
    Route::patch('customer-types/{id}/activate', [CustomerTypeController::class, 'activate']);
    Route::delete('customer-types/{id}/force', [CustomerTypeController::class, 'forceDelete']);
    
    // Customer routes
    Route::apiResource('customers', CustomerController::class);
    Route::get('customers/paginated', [CustomerController::class, 'paginated']);
    Route::get('customers/search', [CustomerController::class, 'search']);
    Route::get('customers/{id}/stats', [CustomerController::class, 'stats']);
    Route::get('customers/by-type/{typeId}', [CustomerController::class, 'byType']);
    Route::patch('customers/{id}/activate', [CustomerController::class, 'activate']);
    Route::delete('customers/{id}/force', [CustomerController::class, 'forceDelete']);
    
    // Product routes
    Route::apiResource('products', ProductController::class);
    Route::get('products/search', [ProductController::class, 'search']);
    Route::get('products/low-stock', [ProductController::class, 'lowStock']);
    
    // Order routes
    Route::apiResource('orders', OrderController::class);
    Route::patch('orders/{id}/status', [OrderController::class, 'updateStatus']);
    Route::patch('orders/{id}/cancel', [OrderController::class, 'cancel']);
    Route::get('orders/revenue/report', [OrderController::class, 'revenue']);
    
    // Discount routes
    Route::apiResource('discounts', DiscountController::class);
    Route::post('discounts/validate', [DiscountController::class, 'validateDiscount']);
    Route::get('discounts/applicable', [DiscountController::class, 'getApplicable']);
    
});
