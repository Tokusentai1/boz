<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    public function getAddress($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(
                [
                    "success" => false,
                    "statusCode" => 400,
                    "error" => "There is no User with this ID " . $id,
                    "result" => null
                ]
            );
        }

        return response()->json(
            [
                "success" => true,
                "statusCode" => 200,
                "error" => null,
                "result" => $user->address
            ]
        );
    }

    public function addAddress(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'city' => 'required',
                'block' => 'required',
                'street' => 'required',
                'building' => 'required',
                'building_number' => 'required',
                'user_id' => 'required'
            ]
        );

        if ($validation->fails()) {
            return response()->json(
                [
                    "success" => false,
                    "statusCode" => 400,
                    "error" => $validation->errors(),
                    "result" => null
                ]
            );
        } else {
            $address = new Address();
            $address->city = $request->city;
            $address->block = $request->block;
            $address->street = $request->street;
            $address->building = $request->building;
            $address->building_number = $request->building_number;
            $address->user_id = $request->user_id;
            $address->save();

            return response()->json(
                [
                    "success" => true,
                    "statusCode" => 201,
                    "error" => null,
                    "result" => $address
                ]
            );
        }
    }

    public function editAddress(Request $request, $id)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'city' => 'required',
                'block' => 'required',
                'street' => 'required',
                'building' => 'required',
                'building_number' => 'required',
            ]
        );

        if ($validation->fails()) {
            return response()->json(
                [
                    "success" => false,
                    "statusCode" => 400,
                    "error" => $validation->errors(),
                    "result" => null
                ]
            );
        } else {
            $address = Address::where('user_id', $id)->first();

            if (!$address) {
                return response()->json(
                    [
                        "success" => false,
                        "statusCode" => 400,
                        "error" => "There is no address for this user",
                        "result" => null
                    ]
                );
            }

            $address->city = $request->city;
            $address->block = $request->block;
            $address->street = $request->street;
            $address->building = $request->building;
            $address->building_number = $request->building_number;
            $address->save();

            // Retrieve the updated address object
            $updatedAddress = Address::find($address->id);

            if ($updatedAddress) {
                return response()->json(
                    [
                        "success" => true,
                        "statusCode" => 200,
                        "error" => null,
                        "result" => $updatedAddress
                    ]
                );
            } else {
                return response()->json(
                    [
                        "success" => false,
                        "statusCode" => 400,
                        "error" => "Failed to retrieve updated address",
                        "result" => null
                    ]
                );
            }
        }
    }

    public function deleteAddress($id)
    {
        $address = Address::where('user_id', $id)->first();

        if (!$address) {
            return response()->json(
                [
                    "success" => false,
                    "statusCode" => 400,
                    "error" => "No address found for this user",
                    "result" => null
                ]
            );
        }

        $deleted = $address->delete();

        if ($deleted) {
            return response()->json(
                [
                    "success" => true,
                    "statusCode" => 200,
                    "error" => null,
                    "result" => "Address deleted"
                ]
            );
        } else {
            return response()->json(
                [
                    "success" => false,
                    "statusCode" => 400,
                    "error" => "Failed to delete address. Please try again later.",
                    "result" => null
                ]
            );
        }
    }
}
