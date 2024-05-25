<?php

namespace App\Http\Controllers;

use App\Models\category;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{

    public function getCategory($limit)
    {
        $categories = Category::select('id', 'name', 'picture')->limit($limit)->get();

        foreach ($categories as $category) {
            $category->name = trans("categories.{$category->name}");

            $category->picture = Storage::url('category/' . $category->picture);
        }

        return response()->json([
            "Category" => $categories,
            "Status" => 200,
        ]);
    }

    public function getSubCategory($id, $limit)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(
                [
                    "message" => "Category not found",
                    "Status" => 404,
                ]
            );
        }

        // Retrieve subcategories with a limit
        $subCategories = $category->subCategories()
            ->select('id', 'name', 'picture', 'category_id')
            ->limit($limit)
            ->get();

        foreach ($subCategories as $subCategory) {
            $subCategory->name = trans("sub_categories.{$subCategory->name}");

            $subCategory->image_url = Storage::url('subCategory/' . $subCategory->picture);
        }

        return response()->json(
            [
                "Sub_Category" => $subCategories,
                "Status" => 200,
            ]
        );
    }
}
