<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ImageUploadController extends Controller
{
    /**
     * Upload an image for a product.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadProductImage(Request $request, $productId)
    {
        // Find the product
        $product = Product::find($productId);
        
        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        // Validate the image
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'alt_text' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Process and store the image
            $uploadedFile = $request->file('image');
            $originalFilename = $uploadedFile->getClientOriginalName();
            $extension = $uploadedFile->getClientOriginalExtension();
            
            // Generate a unique filename
            $filename = Str::slug(pathinfo($originalFilename, PATHINFO_FILENAME)) . '_' . time() . '.' . $extension;
            
            // Define storage paths
            $storagePath = 'products';
            $relativePath = 'storage/' . $storagePath;
            
            // Store the original image
            $path = $uploadedFile->storeAs($storagePath, $filename, 'public');
            
            // Create a thumbnail
            $thumbnail = 'thumb_' . $filename;
            $thumbnailPath = storage_path('app/public/' . $storagePath . '/' . $thumbnail);
            
            // Create the intervention/image instance
            $image = Image::make($uploadedFile);
            
            // Resize the image for thumbnail while maintaining aspect ratio
            $image->resize(150, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })->save($thumbnailPath);
            
            // Update product with image info
            $product->update([
                'image_filename' => $filename,
                'image_path' => $relativePath,
                'image_alt' => $request->alt_text ?? $product->name,
                'image_thumbnail' => $thumbnail,
                'image_url' => url($relativePath . '/' . $filename), // Also update the original image_url
            ]);

            return response()->json([
                'message' => 'Image uploaded successfully',
                'data' => [
                    'product_id' => $product->id,
                    'image_url' => $product->image_url,
                    'image_filename' => $product->image_filename,
                    'image_path' => $product->image_path,
                    'image_alt' => $product->image_alt,
                    'image_thumbnail' => url($relativePath . '/' . $product->image_thumbnail),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Image upload failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Delete a product image.
     *
     * @param  int  $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteProductImage($productId)
    {
        $product = Product::find($productId);
        
        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        // Check if the product has image information
        if ($product->image_filename && $product->image_path) {
            // Extract just the storage path without the 'storage/' prefix
            $storagePath = str_replace('storage/', '', $product->image_path);
            
            // Delete the original image
            if (Storage::disk('public')->exists($storagePath . '/' . $product->image_filename)) {
                Storage::disk('public')->delete($storagePath . '/' . $product->image_filename);
            }
            
            // Delete the thumbnail if it exists
            if ($product->image_thumbnail && Storage::disk('public')->exists($storagePath . '/' . $product->image_thumbnail)) {
                Storage::disk('public')->delete($storagePath . '/' . $product->image_thumbnail);
            }
            
            // Reset image fields
            $product->update([
                'image_filename' => null,
                'image_path' => null,
                'image_alt' => null,
                'image_thumbnail' => null,
                'image_url' => null,
            ]);
            
            return response()->json([
                'message' => 'Product image deleted successfully'
            ]);
        }
        
        return response()->json([
            'message' => 'Product has no image to delete'
        ]);
    }
} 