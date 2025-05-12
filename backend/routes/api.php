<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\AdminProductController;

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
        
        // Image upload routes - would typically be admin-only in production
        Route::post('/products/{id}/image', [ImageUploadController::class, 'uploadProductImage']);
        Route::delete('/products/{id}/image', [ImageUploadController::class, 'deleteProductImage']);
        
        // Admin routes
        // In a real application, you would add another middleware to check if user is admin
        Route::group(['prefix' => 'admin'], function () {
            // Admin Product CRUD
            Route::get('/products', [AdminProductController::class, 'index']);
            Route::post('/products', [AdminProductController::class, 'store']);
            Route::get('/products/{id}', [AdminProductController::class, 'show']);
            Route::put('/products/{id}', [AdminProductController::class, 'update']);
            Route::delete('/products/{id}', [AdminProductController::class, 'destroy']);
        });
    });
}); 