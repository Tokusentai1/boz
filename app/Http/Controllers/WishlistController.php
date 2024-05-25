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
            return response()->json(
                [
                    'Message' => 'User not found',
                    "Status" => 404
                ]
            );
        }

        $product = Product::find($request->product_id);

        if (!$product) {
            return response()->json(
                [
                    "Message" => 'Product not found',
                    "Status" => 404
                ]
            );
        }

        $wishlist = $user->wishlist()->firstOrCreate([]);

        $existingProduct = $wishlist->products()->where('product_id', $request->product_id)->exists();

        if ($existingProduct) {
            $wishlist->products()->detach($request->product_id);

            return response()->json(
                [
                    "Message" => 'Product removed from wishlist',
                    "Status" => 200
                ]
            );
        } else {
            $wishlist->products()->attach($product, ['quantity' => $request->quantity ?? 1]);
            return response()->json(
                [
                    "Message" => 'Product added to wishlist',
                    "Status" => 201
                ],
            );
        }
    }

    public function getWishlist($id)
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

        $products = [];

        if ($user->wishlist) {
            $products = $user->wishlist->products()->select(
                'products.id',
                'products.name',
                'products.description',
                'products.picture',
                'product_wishlist.quantity',
                'products.price',
                'products.calories'
            )->get()->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => trans("products.name.{$product->name}"),
                    'description' => trans("products.description.{$product->description}"),
                    'picture' => Storage::url('product/' . $product->picture),
                    'quantity' => $product->pivot->quantity,
                    'price' => $product->price,
                    'calories' => $product->calories,
                ];
            })->toArray();
        }

        return response()->json(
            [
                "Wishlist" => $products,
                "Status" => 200
            ]
        );
    }
}
