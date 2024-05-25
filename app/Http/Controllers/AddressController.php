<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Address;
use Illuminate\Http\Request;
use Validator;

class AddressController extends Controller
{
    public function getAddress($id)
    {
        return response()->json(
            [
                "Address" => User::find($id)->address,
                "Status" => 200,
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
                    "Message" => $validation->errors(),
                    "Status" => 400,
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
                    "Message" => "address added",
                    "Status" => 201
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
                    "Message" => $validation->errors(),
                    "Status" => 400,
                ]
            );
        } else {
            $address = Address::where('user_id', '=', $id);
            $res = $address->update(
                [
                    'city' => $request->city,
                    'block' => $request->block,
                    'street' => $request->street,
                    'building' => $request->building,
                    'building_number' => $request->building_number,
                ]
            );
            if ($res) {
                return response()->json(
                    [
                        "Message" => "address updated",
                        "Status" => 200,
                    ]
                );
            } else {
                return response()->json(
                    [
                        "Message" => 'There is no address for this user',
                        "Status" => 400,
                    ]
                );
            }
        }
    }

    public function deleteAddress($id)
    {
        Address::where('user_id', '=', $id)->delete();
        return response()->json(
            [
                "Message" => "Address deleted",
                "Status" => 200
            ]
        );
    }
}
