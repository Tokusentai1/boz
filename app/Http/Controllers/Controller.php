<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Carbon;
use Validator;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function login(Request $request)
    {
        $email = strtolower($request->email);
        $user = User::where("email", $email)->first();

        if (!$user || !Hash::check($request->password, $user->getAuthPassword())) {
            return response()->json(
                [
                    "Message" => "No user found",
                    "Status" => 400
                ]
            );
        } else {
            return response()->json(
                [
                    "Message" => "Logging in user",
                    "Status" => 200
                ]
            );
        }
    }

    public function signUp(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email',
                'password' => 'required',
                'phone_number' => 'required',
            ]
        );

        if ($validation->fails()) {
            return response()->json(
                [
                    "Message" => $validation->errors(),
                    "Status" => 400
                ]
            );
        } else {
            $user = new User();
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = strtolower($request->email);
            $user->password = bcrypt($request->password);
            $user->phone_number = $request->phone_number;
            $user->save();

            return response()->json(
                [
                    "Message" => "User created",
                    "Status" => 201,
                ]
            );
        }
    }

    public function update(Request $request, $id)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email',
                'password' => 'required',
                'phone_number' => 'required',
            ]
        );

        if ($validation->fails()) {
            return response()->json(
                [
                    "Message" => $validation->errors(),
                    "Status" => 400
                ]
            );
        } else {
            $user = User::find($id);
            if ($user) {
                $user->update(
                    [
                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'email' => strtolower($request->email),
                        'password' => $request->password,
                        'phone_number' => $request->phone_number,
                    ]
                );
                return response()->json(
                    [
                        "Message" => "User updated",
                        "Status" => 200,
                    ]
                );
            } else {
                return response()->json(
                    [
                        "Message" => "There is no User with this ID " . $id,
                        "Status" => 400
                    ]
                );
            }
        }
    }

    public function delete($id)
    {
        User::find($id)->delete();
        return response()->json(
            [
                "Message" => "User deleted",
                "Status" => 200
            ]
        );
    }
}
