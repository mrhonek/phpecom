<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    /**
     * Display the user's cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCart(Request $request)
    {
        $cartItems = CartItem::with('product')
            ->where('user_id', $request->user()->id)
            ->get();
        
        $totalPrice = 0;
        foreach ($cartItems as $item) {
            $totalPrice += $item->product->price * $item->quantity;
        }
        
        return response()->json([
            'data' => $cartItems,
            'total' => $totalPrice
        ]);
    }

    /**
     * Add a product to the cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $product = Product::find($request->product_id);
        
        if ($product->stock < $request->quantity) {
            return response()->json([
                'message' => 'Not enough stock available'
            ], 400);
        }
        
        // Check if the product is already in the cart
        $existingItem = CartItem::where('user_id', $request->user()->id)
            ->where('product_id', $request->product_id)
            ->first();
            
        if ($existingItem) {
            // Update quantity
            $existingItem->quantity += $request->quantity;
            $existingItem->save();
            
            return response()->json([
                'message' => 'Cart updated successfully',
                'data' => $existingItem
            ]);
        }
        
        // Create new cart item
        $cartItem = CartItem::create([
            'user_id' => $request->user()->id,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity
        ]);
        
        return response()->json([
            'message' => 'Item added to cart successfully',
            'data' => $cartItem
        ], 201);
    }

    /**
     * Remove an item from the cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeItem(Request $request, $id)
    {
        $cartItem = CartItem::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();
            
        if (!$cartItem) {
            return response()->json([
                'message' => 'Cart item not found'
            ], 404);
        }
        
        $cartItem->delete();
        
        return response()->json([
            'message' => 'Item removed from cart successfully'
        ]);
    }

    /**
     * Update the quantity of an item in the cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateQuantity(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $cartItem = CartItem::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();
            
        if (!$cartItem) {
            return response()->json([
                'message' => 'Cart item not found'
            ], 404);
        }
        
        $product = Product::find($cartItem->product_id);
        
        if ($product->stock < $request->quantity) {
            return response()->json([
                'message' => 'Not enough stock available'
            ], 400);
        }
        
        $cartItem->quantity = $request->quantity;
        $cartItem->save();
        
        return response()->json([
            'message' => 'Cart item updated successfully',
            'data' => $cartItem
        ]);
    }
} 