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

    // Simple admin health check
    Route::get('/admin-health', function () {
        return response()->json(['status' => 'ok', 'message' => 'Admin routes are working']);
    });
    
    // Direct simple product image update
    Route::get('/update-images', function () {
        $updatedCount = 0;
        for ($i = 1; $i <= 10; $i++) {
            $product = \App\Models\Product::find($i);
            if ($product) {
                $product->update([
                    'image_filename' => 'product-' . $i . '.jpg',
                    'image_path' => 'images/products',
                    'image_alt' => 'Product ' . $i,
                    'image_thumbnail' => 'product-' . $i . '-thumb.jpg',
                ]);
                $updatedCount++;
            }
        }
        
        return [
            'status' => 'success',
            'message' => "Updated {$updatedCount} products with image data",
        ];
    });

    // Public routes
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    
    // Temporary route to update product images - REMOVE AFTER USE
    Route::get('/admin/update-product-images', function () {
        // The products data
        $products = [
            [
                'id' => 1,
                'image_filename' => 'smartphone-xs-pro.jpg',
                'image_path' => 'images/products',
                'image_alt' => 'Smartphone XS Pro on a wooden surface',
                'image_thumbnail' => 'smartphone-xs-pro-thumb.jpg',
            ],
            [
                'id' => 2,
                'image_filename' => 'ultra-hd-tv.jpg',
                'image_path' => 'images/products',
                'image_alt' => 'Ultra HD Smart TV in modern living room',
                'image_thumbnail' => 'ultra-hd-tv-thumb.jpg',
            ],
            [
                'id' => 3,
                'image_filename' => 'wireless-headphones.jpg',
                'image_path' => 'images/products',
                'image_alt' => 'Wireless noise-cancelling headphones in black',
                'image_thumbnail' => 'wireless-headphones-thumb.jpg',
            ],
            [
                'id' => 4,
                'image_filename' => 'digital-camera.jpg',
                'image_path' => 'images/products',
                'image_alt' => 'Professional digital camera with lens',
                'image_thumbnail' => 'digital-camera-thumb.jpg',
            ],
            [
                'id' => 5,
                'image_filename' => 'bluetooth-speaker.jpg',
                'image_path' => 'images/products',
                'image_alt' => 'Portable bluetooth speaker in blue color',
                'image_thumbnail' => 'bluetooth-speaker-thumb.jpg',
            ],
            [
                'id' => 6,
                'image_filename' => 'fitness-smartwatch.jpg',
                'image_path' => 'images/products',
                'image_alt' => 'Fitness smartwatch showing heart rate',
                'image_thumbnail' => 'fitness-smartwatch-thumb.jpg',
            ],
            [
                'id' => 7,
                'image_filename' => 'coffee-grinder.jpg',
                'image_path' => 'images/products',
                'image_alt' => 'Electric coffee grinder with coffee beans',
                'image_thumbnail' => 'coffee-grinder-thumb.jpg',
            ],
            [
                'id' => 8,
                'image_filename' => 'mechanical-keyboard.jpg',
                'image_path' => 'images/products',
                'image_alt' => 'Mechanical keyboard with RGB lighting',
                'image_thumbnail' => 'mechanical-keyboard-thumb.jpg',
            ],
            [
                'id' => 9,
                'image_filename' => 'office-chair.jpg',
                'image_path' => 'images/products',
                'image_alt' => 'Ergonomic office chair in black',
                'image_thumbnail' => 'office-chair-thumb.jpg',
            ],
            [
                'id' => 10,
                'image_filename' => 'smart-home-hub.jpg',
                'image_path' => 'images/products',
                'image_alt' => 'Smart home hub on a coffee table',
                'image_thumbnail' => 'smart-home-hub-thumb.jpg',
            ],
        ];

        // Update each product directly
        $updatedCount = 0;
        foreach ($products as $productData) {
            $product = \App\Models\Product::find($productData['id']);
            if ($product) {
                $product->update([
                    'image_filename' => $productData['image_filename'],
                    'image_path' => $productData['image_path'],
                    'image_alt' => $productData['image_alt'],
                    'image_thumbnail' => $productData['image_thumbnail'],
                ]);
                $updatedCount++;
            }
        }

        return [
            'status' => 'success',
            'message' => "Updated {$updatedCount} products with image data",
        ];
    });

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