<?php

namespace App\Http\Controllers;

use App\Models\cart;
use App\Models\product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CartController extends Controller
{
    public function addCart(Request $request)
    {
        $user = User::find($request->user_id);
        $product = Product::find($request->product_id);

        $totalPrice = $request->quantity * $product->price;
        $cart = Cart::create([
            'user_id' => $user->id,
            'totalPrice' => $totalPrice,
        ]);

        $cart->products()->attach($product->id, ['quantity' => $request->quantity]);

        DB::commit();

        return response()->json([
            "message" => 'Product added to cart',
            "status" => 201
        ]);
        DB::rollBack();

        return response()->json([
            "message" => 'Error adding product to cart',
            "status" => 400
        ]);
    }

    public function getCart($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(
                [
                    "Message" => 'User not found',
                    "Status" => 404
                ]
            );
        }

        $cart = $user->carts()->orderBy('created_at', 'desc')->first();

        if (!$cart) {
            return response()->json(
                [
                    "Message" => 'Cart not found',
                    "Status" => 404
                ]
            );
        }

        $products = $cart->products()->select(
            'products.id',
            'products.name',
            'products.description',
            'products.picture',
            'cart_product.quantity',
            'products.price'
        )->get()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => trans("products.name.{$product->name}"),
                'description' => trans("products.description.{$product->description}"),
                'picture' => Storage::url('product/' . $product->picture),
                'quantity' => $product->pivot->quantity,
                'price' => $product->price,
                'total_price' => $product->pivot->quantity * $product->price,
            ];
        });

        // Calculate the overall total price for the cart
        $totalPrice = $products->sum('total_price');

        return response()->json([
            "Cart_id" => $cart->id,
            "Cart" => $products,
            "TotalPrice" => $totalPrice,
            "Status" => 200
        ]);
    }


    public function deleteCart($id)
    {
        $cart = Cart::find($id);

        if (!$cart) {
            return response()->json(
                [
                    "Message" => 'Cart not found',
                    "Status" => 404
                ]
            );
        }

        $cart->products()->detach();

        $cart->delete();

        return response()->json(
            [
                "Message" => 'Cart deleted successfully',
                "Status" => 200
            ]
        );
    }

    public function updateCart(Request $request)
    {
        $cart = Cart::find($request->cart_id);

        if (!$cart) {
            return response()->json([
                "Message" => 'Cart not found',
                "Status" => 404
            ]);
        }

        $product = Product::find($request->product_id);

        if (!$product) {
            return response()->json([
                "Message" => 'Product not found',
                "Status" => 404
            ]);
        }

        // Check if the product already exists in the cart
        $existingProduct = $cart->products()->where('product_id', $product->id)->first();

        if ($existingProduct) {
            // Calculate new quantity
            $newQuantity = $existingProduct->pivot->quantity + $request->quantity;

            if ($newQuantity <= 0) {
                // If the new quantity is 0 or negative, remove the product from the cart
                $cart->products()->detach($product->id);
            } else {
                // Otherwise, update the quantity
                $cart->products()->updateExistingPivot($product->id, ['quantity' => $newQuantity]);
            }
        } else {
            // If the product is not found in the cart, but the quantity is positive, add it to the cart
            if ($request->quantity > 0) {
                $cart->products()->attach($product->id, ['quantity' => $request->quantity]);
            }
        }

        // Update the total price
        $cart->totalPrice = $cart->products->sum(function ($product) {
            return $product->pivot->quantity * $product->price;
        });
        $cart->save();

        return response()->json([
            "Message" => 'Cart updated successfully',
            "Status" => 200
        ]);
    }

    public function removeProduct(Request $request)
    {
        // Find the cart
        $cart = Cart::find($request->cart_id);

        // Check if the cart exists
        if (!$cart) {
            return response()->json(
                [
                    "Message" => 'Cart not found',
                    "Status" => 404
                ]
            );
        }

        // Check if the product exists in the cart
        if (!$cart->products()->where('product_id', $request->product_id)->exists()) {
            return response()->json(
                [
                    "Message" => 'Product not found in the cart',
                    "Status" => 404
                ]
            );
        }

        // Detach the product from the cart
        $cart->products()->detach($request->product_id);

        // Check if the cart is empty
        if ($cart->products()->count() == 0) {
            $cart->delete();
            return response()->json(
                [
                    "Message" => 'Product removed and cart deleted as it was empty',
                    "Status" => 200
                ]
            );
        }

        return response()->json(
            [
                "Message" => 'Product removed from cart successfully',
                "Status" => 200
            ]
        );
    }
}
