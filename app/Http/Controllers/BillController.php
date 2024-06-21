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

        if (!$user) {
            return response()->json(
                [
                    "success" => false,
                    "statusCode" => 400,
                    "error" => "User not found",
                    "result" => null
                ]
            );
        }

        return response()->json(
            [
                "success" => true,
                "statusCode" => 200,
                "error" => null,
                "result" => $user->bills
            ]
        );
    }

    public function getBillProduct($billId)
    {
        $bill = Bill::find($billId);

        if (!$bill) {
            return response()->json(
                [
                    "success" => false,
                    "statusCode" => 404,
                    "error" => "Bill not found",
                    "result" => null
                ]
            );
        }

        $cart = $bill->cart;

        if (!$cart) {
            return response()->json(
                [
                    "success" => false,
                    "statusCode" => 404,
                    "error" => "Cart not found for this bill",
                    "result" => null
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
                //edit this here to get the correct url
                'picture' => Storage::url('product/' . $product->picture),
                'quantity' => $product->pivot->quantity,
                'price' => $product->price,
                'calories' => $product->calories,
                'status' => $bill->status,
            ];
        });

        return response()->json(
            [
                "success" => true,
                "statusCode" => 200,
                "error" => null,
                "result" => $billProducts
            ]
        );
    }

    public function addBill($id)
    {
        $cart = Cart::find($id);

        if (!$cart) {
            return response()->json(
                [
                    "success" => false,
                    "statusCode" => 404,
                    "error" => "Cart not found",
                    "result" => null
                ]
            );
        }

        $user = $cart->user;

        if (!$user) {
            return response()->json(
                [
                    "success" => false,
                    "statusCode" => 404,
                    "error" => "User not found for this cart",
                    "result" => null
                ]
            );
        }

        $existingBill = Bill::where('cart_id', $id)->first();

        if ($existingBill) {
            return response()->json(
                [
                    "success" => false,
                    "statusCode" => 400,
                    "error" => "A bill already exists for this cart",
                    "result" => null
                ]
            );
        }

        $cartProducts = $cart->products;

        foreach ($cartProducts as $product) {
            $quantityInCart = $product->pivot->quantity;

            $product->quantity -= $quantityInCart;

            if ($product->quantity < 0) {
                // Rollback quantity change if there's not enough inventory
                $product->quantity += $quantityInCart;
                $product->save();

                return response()->json(
                    [
                        "success" => false,
                        "statusCode" => 400,
                        "error" => "Insufficient product quantity for " . $product->name,
                        "result" => null
                    ]
                );
            }
        }

        $bill = new Bill();
        $bill->cart_id = $id;
        $bill->user_id = $user->id;
        $bill->save();

        return response()->json(
            [
                "success" => true,
                "statusCode" => 201,
                "error" => null,
                "result" => $bill
            ]
        );
    }

    public function cancelBill($billId)
    {
        $bill = Bill::find($billId);

        if (!$bill) {
            return response()->json(
                [
                    "success" => false,
                    "statusCode" => 404,
                    "error" => "Bill not found",
                    "result" => null
                ]
            );
        }

        $currentTime = now();
        $timeDifferenceInMinutes = $currentTime->diffInMinutes($bill->created_at);

        if ($timeDifferenceInMinutes > 15) {
            return response()->json(
                [
                    'success' => false,
                    "statusCode" => 400,
                    'error' => 'Cannot cancel bill. Time limit exceeded.',
                    "result" => null
                ]
            );
        }

        $bill->status = 'cancelled';
        $bill->save();

        return response()->json(
            [
                'success' => true,
                "statusCode" => 201,
                'error' => null,
                'result' => $bill
            ]
        );
    }
}
