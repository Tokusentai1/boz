<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WishlistController extends Controller
{
    public function addToWishlist(Request $request)
    {
        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'statusCode' => 404,
                'error' => 'User not found',
                'result' => null
            ]);
        }

        $product = Product::find($request->product_id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'statusCode' => 404,
                'error' => 'Product not found',
                'result' => null
            ]);
        }

        $wishlist = $user->wishlist()->firstOrCreate([]);

        $wishlist->products()->syncWithoutDetaching($product->id);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'error' => null,
            'result' => 'Product added to wishlist',
        ]);
    }

    public function removeFromWishlist(Request $request)
    {
        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'statusCode' => 404,
                'error' => 'User not found',
                'result' => null
            ]);
        }

        $product = Product::find($request->product_id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'statusCode' => 404,
                'error' => 'Product not found',
                'result' => null
            ]);
        }

        $wishlist = $user->wishlist;

        if ($wishlist && $wishlist->products()->where('product_id', $product->id)->exists()) {
            $wishlist->products()->detach($product->id);

            return response()->json([
                'success' => true,
                'statusCode' => 200,
                'error' => null,
                'result' => 'Product removed from wishlist',
            ]);
        }

        return response()->json([
            'success' => false,
            'statusCode' => 404,
            'error' => 'Product not found in wishlist',
            'result' => null,
        ]);
    }

    public function getWishlist($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'statusCode' => 404,
                'error' => 'User not found',
                'result' => null
            ]);
        }

        $products = [];

        if ($user->wishlist) {
            $products = $user->wishlist->products()->select(
                'products.id',
                'products.name',
                'products.description',
                'products.picture',
                'products.price',
                'products.calories'
            )->get()->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => trans("products.name.{$product->name}"),
                    'description' => trans("products.description.{$product->description}"),
                    'picture' => Storage::url('product/' . $product->picture),
                    'price' => $product->price,
                    'calories' => $product->calories,
                ];
            })->toArray();
        }

        return response()->json([
            'success' => true,
            'statusCode' => 200,
            'error' => null,
            'result' => $products
        ]);
    }
}
