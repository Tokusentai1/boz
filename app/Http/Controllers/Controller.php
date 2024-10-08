<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;


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
                    "success" => false,
                    "statusCode" => 400,
                    "error" => "No User found with the follwing email and password",
                    "result" => null
                ]
            );
        } else {
            $user->makeHidden('password');
            return response()->json(
                [
                    "success" => true,
                    "statusCode" => 200,
                    "error" => null,
                    "result" => $user
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
                    "success" => false,
                    "statusCode" => 400,
                    "error" => $validation->errors(),
                    "result" => null
                ]
            );
        }

        if (User::where('email', $request->email)->exists()) {
            return response()->json(
                [
                    "success" => false,
                    "statusCode" => 400,
                    "error" => "Email already exists",
                    "result" => null
                ]
            );
        }

        if (User::where('phone_number', $request->phone_number)->exists()) {
            return response()->json(
                [
                    "success" => false,
                    "statusCode" => 400,
                    "error" => "Phone number already exists",
                    "result" => null
                ]
            );
        }

        $user = new User();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = strtolower($request->email);
        $user->password = bcrypt($request->password);
        $user->phone_number = $request->phone_number;
        $user->save();

        $user->makeHidden('password');
        return response()->json(
            [
                "success" => true,
                "statusCode" => 201,
                "error" => null,
                "result" => $user
            ]
        );
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
                    "success" => false,
                    "statusCode" => 400,
                    "error" => $validation->errors(),
                    "result" => null
                ]
            );
        }

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

        if (User::where('email', $request->email)->where('id', '!=', $id)->exists()) {
            return response()->json(
                [
                    "success" => false,
                    "statusCode" => 400,
                    "error" => "Email already exists",
                    "result" => null
                ]
            );
        }

        if (User::where('phone_number', $request->phone_number)->where('id', '!=', $id)->exists()) {
            return response()->json(
                [
                    "success" => false,
                    "statusCode" => 400,
                    "error" => "Phone number already exists",
                    "result" => null
                ]
            );
        }

        $user->update(
            [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => strtolower($request->email),
                'password' => bcrypt($request->password),
                'phone_number' => $request->phone_number,
            ]
        );

        $user->makeHidden('password');

        return response()->json(
            [
                "success" => true,
                "statusCode" => 200,
                "error" => null,
                "result" => $user
            ]
        );
    }

    public function delete($id)
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

        $user->delete();

        return response()->json(
            [
                "success" => true,
                "statusCode" => 200,
                "error" => null,
                "result" => "User deleted"
            ]
        );
    }

    public function translation(Request $request)
    {
        $request->validate([
            'language' => 'required|string|in:en,ar',
            'file' => 'required|string|in:categories,sub_categories',
            'key' => 'required|string',
            'value' => 'required|string',
        ]);

        $language = $request->input('language');
        $file = $request->input('file');
        $key = $request->input('key');
        $value = $request->input('value');

        $filePath = resource_path("lang/{$language}/{$file}.php");

        if (!File::exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $translations = include $filePath;
        $translations[$key] = $value;

        $content = '<?php' . PHP_EOL . PHP_EOL . 'return ' . var_export($translations, true) . ';' . PHP_EOL;

        File::put($filePath, $content);

        return response()->json(['success' => 'Translation added successfully']);
    }

    public function translationProduct(Request $request)
    {
        $request->validate([
            'language' => 'required|string|in:en,ar',
            'file' => 'required|string|in:products',
            'name' => 'array',
            'name.key' => 'required_with:name|string',
            'name.value' => 'required_with:name|string',
            'description' => 'array',
            'description.key' => 'required_with:description|string',
            'description.value' => 'required_with:description|string',
        ]);

        $language = $request->input('language');
        $file = $request->input('file');
        $name = $request->input('name');
        $description = $request->input('description');

        $filePath = resource_path("lang/{$language}/{$file}.php");

        if (!File::exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $translations = include $filePath;

        if ($file === 'products') {
            // Ensure the sections exist and are arrays
            if (!isset($translations['name']) || !is_array($translations['name'])) {
                $translations['name'] = [];
            }
            if (!isset($translations['description']) || !is_array($translations['description'])) {
                $translations['description'] = [];
            }

            if ($name) {
                $translations['name'][$name['key']] = $name['value'];
            }
            if ($description) {
                $translations['description'][$description['key']] = $description['value'];
            }
        } else {
            if ($name) {
                $translations[$name['key']] = $name['value'];
            }
            if ($description) {
                $translations[$description['key']] = $description['value'];
            }
        }

        $content = '<?php' . PHP_EOL . PHP_EOL . 'return ' . var_export($translations, true) . ';' . PHP_EOL;

        File::put($filePath, $content);

        return response()->json(['success' => 'Translation added successfully']);
    }
}
