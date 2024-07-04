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

        if (!$user) {
            return response()->json(
                [
                    "success" => false,
                    "statusCode" => 404,
                    "error" => "User not found",
                    "result" => null,
                ]
            );
        }

        if (!$product) {
            return response()->json(
                [
                    "success" => false,
                    "statusCode" => 404,
                    "error" => "Product not found",
                    "result" => null,
                ]
            );
        }

        $totalPrice = $request->quantity * $product->price;
        $cart = Cart::create([
            'user_id' => $user->id,
            'totalPrice' => $totalPrice,
        ]);

        if (!$cart) {
            return response()->json(
                [
                    "success" => false,
                    "statusCode" => 400,
                    "error" => "Failed to create cart",
                    "result" => null,
                ]
            );
        }

        $cart->products()->attach($product->id, ['quantity' => $request->quantity]);

        DB::commit();

        return response()->json(
            [
                "success" => true,
                "statusCode" => 201,
                "error" => null,
                "result" => 'Product added to cart',
            ]
        );

        DB::rollBack();

        return response()->json(
            [
                "success" => false,
                "statusCode" => 400,
                "error" => "Error adding product to cart",
                "result" => null,
            ]
        );
    }

    public function getCart($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                "success" => false,
                "statusCode" => 404,
                "error" => "User not found",
                "result" => null,
            ]);
        }

        $cart = $user->carts()->where('active', true)->first();

        if (!$cart) {

            $response = [
                "success" => true,
                "statusCode" => 200,
                "error" => null,
                "result" => [
                    "cart items" => [],
                    'total price' => 0
                ],
            ];

            return response()->json($response);
        }

        $products = $cart->products()->select(
            'products.id',
            'products.name',
            'products.description',
            'products.picture',
            'cart_product.quantity',
            'products.price'
        )->get()->map(function ($product) use ($cart) {
            return [
                'id' => $product->id,
                'name' => trans("products.name.{$product->name}"),
                'description' => trans("products.description.{$product->description}"),
                'picture' => 'https://bozecommerce.sirv.com/product/' . $product->picture,
                'quantity' => $product->pivot->quantity,
                'price' => $product->price,
            ];
        });

        $response = [
            "success" => true,
            "statusCode" => 200,
            "error" => null,
            "result" => [
                "cart items" => $products,
                'total price' => $cart->totalPrice
            ],
        ];

        return response()->json($response);
    }


    public function deleteCart($id)
    {
        $cart = Cart::find($id);

        if (!$cart) {
            return response()->json(
                [
                    "success" => false,
                    "statusCode" => 404,
                    "error" => "Cart not found",
                    "result" => null,
                ]
            );
        }

        $cart->products()->detach();
        $cart->delete();

        return response()->json(
            [
                "success" => true,
                "statusCode" => 200,
                "error" => null,
                "result" => 'Cart deleted successfully'
            ]
        );
    }

    public function updateCart(Request $request)
    {
        $cart = Cart::find($request->cart_id);

        if (!$cart) {
            return response()->json(
                [
                    "success" => false,
                    "statusCode" => 404,
                    "error" => "Cart not found",
                    "result" => null,
                ]
            );
        }

        $product = Product::find($request->product_id);

        if (!$product) {
            return response()->json(
                [
                    "success" => false,
                    "statusCode" => 404,
                    "error" => "Product not found",
                    "result" => null,
                ]
            );
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

        return response()->json(
            [
                "success" => true,
                "statusCode" => 200,
                "error" => null,
                "result" => 'Cart updated successfully',
            ]
        );
    }

    public function removeProduct(Request $request)
    {
        // Find the cart
        $cart = Cart::find($request->cart_id);

        // Check if the cart exists
        if (!$cart) {
            return response()->json(
                [
                    "success" => false,
                    "statusCode" => 404,
                    "error" => "Cart not found",
                    "result" => null,
                ]
            );
        }

        // Check if the product exists in the cart
        if (!$cart->products()->where('product_id', $request->product_id)->exists()) {
            return response()->json(
                [
                    "success" => false,
                    "statusCode" => 404,
                    "error" => "Product not found in the cart",
                    "result" => null,
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
                    "success" => true,
                    "statusCode" => 200,
                    "error" => null,
                    "result" => 'Product removed and cart deleted as it was empty',

                ]
            );
        }

        return response()->json(
            [
                "success" => true,
                "statusCode" => 200,
                "error" => null,
                "result" => 'Product removed from cart successfully',
            ]
        );
    }


    public function createCart(Request $request)
    {
        // Retrieve the request parameters
        $userId = $request->input('user_id');
        $productIds = $request->input('product_ids');
        $quantities = $request->input('quantities');

        // Find the user
        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'success' => false,
                'statusCode' => 404,
                'error' => 'User not found',
                'result' => null
            ]);
        }

        // Check if the user already has an active cart
        $activeCart = $user->carts()->where('active', true)->first();

        if ($activeCart) {
            // If the user has an active cart, update it
            $activeCart->products()->detach(); // Remove all existing products
            $cart = $activeCart;
        } else {
            // If the user does not have an active cart, create a new one
            $cart = Cart::create([
                'user_id' => $userId,
                'totalPrice' => 0, // This will be updated later
                'active' => true // Set the new cart as active
            ]);
        }

        // Initialize the total price
        $totalPrice = 0;

        // Update the cart with products and quantities
        $productErrors = [];

        foreach ($productIds as $index => $productId) {
            $quantity = $quantities[$index];
            $product = Product::find($productId);

            if (!$product) {
                $productErrors[] = "Product with ID {$productId} not found";
                continue;
            }

            // Calculate the total price for this product
            $productTotalPrice = $product->price * $quantity;

            // Attach the product to the cart
            $cart->products()->attach($productId, ['quantity' => $quantity]);

            // Add to the total price of the cart
            $totalPrice += $productTotalPrice;
        }

        // Update the total price of the cart
        $cart->totalPrice = $totalPrice;
        $cart->save();

        // Check if there were any product errors
        if (!empty($productErrors)) {
            return response()->json([
                'success' => false,
                'statusCode' => 400,
                'error' => $productErrors,
                'result' => 'Some products were not found'
            ]);
        }

        return response()->json([
            'success' => true,
            'statusCode' => 200,
            'error' => null,
            'result' => $activeCart ? 'Cart updated ' . $cart->id : 'Cart created ' . $cart->id,
        ]);
    }
}
