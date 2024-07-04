<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\bill;
use Carbon\Carbon;


class BillController extends Controller
{
    public function getBill($id)
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

        $bills = $user->bills->map(function ($bill) {
            $cartPrice = $bill->cart->totalPrice;
            $tax = floor($cartPrice * 0.03);
            $deliveryPrice = 5000;
            $totalPrice = $cartPrice + $tax + $deliveryPrice;

            return [
                'id' => $bill->id,
                'status' => $bill->status,
                'price' => $totalPrice,
                'created_at' => $bill->created_at,
            ];
        });

        $response = [
            "success" => true,
            "statusCode" => 200,
            "error" => null,
            "result" => $bills,
        ];

        return response()->json($response);
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

        $totalPrice = $cart->totalPrice; // Assuming the column name is 'totalprice'

        $billProducts = $products->map(function ($product) use ($bill) {
            return [
                'id' => $product->id,
                'name' => trans("products.name.{$product->name}"),
                'description' => trans("products.description.{$product->description}"),
                'picture' => 'https://bozecommerce.sirv.com/product/' . $product->picture,
                'quantity' => $product->pivot->quantity,
                'price' => $product->price,
                'calories' => $product->calories,
            ];
        });

        $response = [
            "success" => true,
            "statusCode" => 200,
            "error" => null,
            "result" => [
                "bill items" => $billProducts,
                "total_price" => $totalPrice
            ],
        ];

        return response()->json($response);
    }


    public function addBill($userId)
    {
        $user = User::find($userId);

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

        if (!$user->address) {
            return response()->json(
                [
                    "success" => false,
                    "statusCode" => 400,
                    "error" => "User has no address. Please add an address.",
                    "result" => null,
                ]
            );
        }

        $cart = $user->carts()->where('active', true)->first();

        if (!$cart) {
            return response()->json(
                [
                    "success" => false,
                    "statusCode" => 404,
                    "error" => "Active cart not found for this user",
                    "result" => null,
                ]
            );
        }

        $existingBill = Bill::where('cart_id', $cart->id)->first();

        if ($existingBill) {
            return response()->json(
                [
                    "success" => false,
                    "statusCode" => 400,
                    "error" => "A bill already exists for this cart",
                    "result" => null,
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
                        "result" => null,
                    ]
                );
            }
        }

        $bill = new Bill();
        $bill->cart_id = $cart->id;
        $bill->user_id = $user->id;

        // Set the Syrian timezone using Carbon
        $bill->created_at = Carbon::now('Asia/Damascus');

        $bill->save();

        // Deactivate the cart
        $cart->active = false;
        $cart->save();

        return response()->json(
            [
                "success" => true,
                "statusCode" => 201,
                "error" => null,
                "result" => 'Bill created successfully',
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
                'result' => "bill cancelled successfully"
            ]
        );
    }
}
