<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminProductController extends Controller
{
    /**
     * Display a listing of the products for admin.
     * This includes more data than the public endpoint.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $products = Product::latest()->get();
        
        // Product model now has the full_image_url and thumbnail_url auto-appended
        
        return response()->json([
            'data' => $products
        ]);
    }
    
    /**
     * Store a newly created product in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image_url' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $product = Product::create($request->all());
        
        return response()->json([
            'message' => 'Product created successfully',
            'data' => $product
        ], 201);
    }
    
    /**
     * Display the specified product.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }
        
        // Product model now has the full_image_url and thumbnail_url auto-appended
        
        return response()->json([
            'data' => $product
        ]);
    }
    
    /**
     * Update the specified product in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'image_url' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $product->update($request->all());
        
        // Add full image URL
        $product->full_image_url = $product->getFullImageUrlAttribute();
        
        return response()->json([
            'message' => 'Product updated successfully',
            'data' => $product
        ]);
    }
    
    /**
     * Remove the specified product from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }
        
        // Delete associated images if they exist
        if ($product->image_filename && $product->image_path) {
            $storagePath = str_replace('storage/', '', $product->image_path);
            
            // Delete the original image
            if (Storage::disk('public')->exists($storagePath . '/' . $product->image_filename)) {
                Storage::disk('public')->delete($storagePath . '/' . $product->image_filename);
            }
            
            // Delete the thumbnail if it exists
            if ($product->image_thumbnail && Storage::disk('public')->exists($storagePath . '/' . $product->image_thumbnail)) {
                Storage::disk('public')->delete($storagePath . '/' . $product->image_thumbnail);
            }
        }
        
        $product->delete();
        
        return response()->json([
            'message' => 'Product deleted successfully'
        ]);
    }
} 