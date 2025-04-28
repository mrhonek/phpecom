<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Apply CORS middleware to all API routes
Route::middleware(['cors'])->group(function () {
    // Health check endpoint
    Route::get('/health', function () {
        return response()->json(['status' => 'ok', 'version' => '1.0.0']);
    });

    // Public routes
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        
        // Cart routes
        Route::post('/cart/add', [CartController::class, 'addItem']);
        Route::get('/cart', [CartController::class, 'getCart']);
        Route::delete('/cart/{id}', [CartController::class, 'removeItem']);
        Route::put('/cart/{id}', [CartController::class, 'updateQuantity']);
        
        // Order routes
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);
    });
}); 