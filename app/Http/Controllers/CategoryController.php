<?php

namespace App\Http\Controllers;

use App\Models\category;

class CategoryController extends Controller
{
    public function getCategory($index = null, $limit = null)
    {
        // Retrieve all categories if $limit is not provided
        if (!$limit) {
            $categories = Category::select('id', 'name', 'picture')->get();
        } else {
            $query = Category::select('id', 'name', 'picture');

            // Handle pagination if $index is provided
            if ($index !== null) {
                $query->skip($index);
            }

            $categories = $query->take($limit)->get();
        }

        // Transform the categories data
        $categories->transform(function ($category) {
            $category->name = trans("categories.{$category->name}");
            $category->picture = "https://bozecommerce.sirv.com/category/" . $category->picture;
            return $category;
        });

        // Determine if there are more categories to fetch
        $totalCategories = Category::count();
        $hasMore = ($index !== null && $limit !== null) ? ($index + $limit) < $totalCategories : false;

        return response()->json([
            "Category" => $categories,
            "hasMore" => $hasMore,
            "Status" => 200,
        ]);
    }

    public function getSubCategory($id, $index = null, $limit = null)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                "message" => "Category not found",
                "Status" => 404,
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
        $subCategories->transform(function ($subCategory) {
            $subCategory->name = trans("sub_categories.{$subCategory->name}");
            $subCategory->picture = "https://bozecommerce.sirv.com/subCategory/" . $subCategory->picture;
            return $subCategory;
        });

        // Determine if there are more subcategories to fetch
        $totalSubCategories = $category->subCategories()->count();
        $hasMore = ($index !== null && $limit !== null) ? ($index + $limit) < $totalSubCategories : false;

        return response()->json([
            "Sub_Category" => $subCategories,
            "hasMore" => $hasMore,
            "Status" => 200,
        ]);
    }
}
