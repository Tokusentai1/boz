<?php

namespace App\Http\Controllers;

use App\Models\category;

class CategoryController extends Controller
{
    public function getCategory($index = null, $limit = null)
    {
        if (!$limit) {
            $categories = Category::select('id', 'name', 'picture')->get();
        } else {
            $query = Category::select('id', 'name', 'picture');

            if ($index !== null) {
                $query->skip($index);
            }

            $categories = $query->take($limit)->get();
        }

        $categories->transform(function ($category) use ($index, $limit) {
            $category->name = trans("categories.{$category->name}");
            $category->picture = "https://bozecommerce.sirv.com/category/{$category->picture}";
            $category->hasMore = ($index !== null && $limit !== null) ? ($index + $limit) < Category::count() : false;
            return $category;
        });

        return response()->json([
            'success' => true,
            'statusCode' => 200,
            'error' => null,
            'result' => $categories,
        ]);
    }


    public function getSubCategory($id, $index = null, $limit = null)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                "success" => false,
                "statusCode" => 404,
                "error" => "Category not found",
                "result" => null,
            ]);
        }

        $query = $category->subCategories()->select('id', 'name', 'picture', 'category_id');

        // Check if index and limit are provided for pagination
        if ($index !== null && $limit !== null) {
            $query->skip($index)->take($limit);
        }

        // Retrieve subcategories
        $subCategories = $query->get();

        // Transform the subcategories
        $subCategories->transform(function ($subCategory) use ($index, $limit, $category) {
            $subCategory->name = trans("sub_categories.{$subCategory->name}");
            $subCategory->picture = "https://bozecommerce.sirv.com/subCategory/" . $subCategory->picture;
            $subCategory->hasMore = ($index !== null && $limit !== null) ? ($index + $limit) < $category->subCategories()->count() : false;
            return $subCategory;
        });

        return response()->json([
            "success" => true,
            "statusCode" => 200,
            "error" => null,
            "result" => $subCategories,
        ]);
    }
}
