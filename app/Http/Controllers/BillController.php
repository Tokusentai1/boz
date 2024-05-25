<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\bill;
use App\Models\cart;
use Illuminate\Support\Facades\Storage;

class BillController extends Controller
{
    public function getBill($id)
    {
        $user = User::find($id);
        return response()->json(
            [
                "Bill" => $user->bills,
                "Status" => 200
            ]
        );
    }


    public function getBillProduct($billId)
    {
        $bill = Bill::find($billId);

        if (!$bill) {
            return response()->json(
                [
                    "Message" => 'Bill not found',
                    "Status" => 404
                ]
            );
        }

        $cart = $bill->cart;

        if (!$cart) {
            return response()->json(
                [
                    "Message" => 'Cart not found for the bill',
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
            'products.price',
            'products.calories'
        )->get();

        $billProducts = $products->map(function ($product) use ($bill) {
            return [
                'id' => $product->id,
                'name' => trans("products.name.{$product->name}"),
                'description' => trans("products.description.{$product->description}"),
                'picture' => Storage::url('product/' . $product->picture),
                'quantity' => $product->pivot->quantity,
                'price' => $product->price,
                'calories' => $product->calories,
                'status' => $bill->status,
            ];
        });

        return response()->json(
            [
                "Bill Product" => $billProducts,
                "Status" => 200
            ]
        );
    }

    public function addBill($id)
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

        $user = $cart->user;

        if (!$user) {
            return response()->json(
                [
                    "Message" => 'User not found for the cart',
                    "Status" => 404
                ]
            );
        }

        $existingBill = Bill::where('cart_id', $id)->first();

        if ($existingBill) {
            return response()->json(
                [
                    "Message" => 'A bill already exists for this cart',
                    "Status" => 400
                ]
            );
        }

        $cartProducts = $cart->products;

        foreach ($cartProducts as $product) {
            $quantityInCart = $product->pivot->quantity;

            $product->quantity -= $quantityInCart;

            if ($product->quantity < 0) {
                return response()->json(
                    [
                        "Message" => 'Insufficient product quantity for ' . $product->name,
                        "Status" => 400
                    ]
                );
            }

            $product->save();
        }

        $bill = new Bill();
        $bill->cart_id = $id;
        $bill->user_id = $user->id;
        $bill->save();

        return response()->json(
            [
                "Message" => 'Bill created successfully',
                "Status" => 201
            ]
        );
    }


    public function cancelBill($billId)
    {
        $bill = Bill::find($billId);

        if (!$bill) {
            return response()->json(
                [
                    "Message" => 'Bill not found',
                    "Status" => 404
                ]
            );
        }

        $currentTime = now();
        $timeDifferenceInMinutes = $currentTime->diffInMinutes($bill->created_at);

        if ($timeDifferenceInMinutes > 15) {
            return response()->json(
                [
                    'Message' => 'Cannot cancel bill. Time limit exceeded.',
                    "Status" => 400
                ]
            );
        }

        $bill->status = 'cancelled';
        $bill->save();

        return response()->json(
            [
                'Message' => 'Bill canceled successfully',
                "Status" => 201
            ]
        );
    }
}
