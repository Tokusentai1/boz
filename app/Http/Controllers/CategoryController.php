<?php

namespace App\Http\Controllers;

use App\Models\category;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{

    public function getCategory($limit)
    {
        $categories = Category::select('id', 'name', 'picture')->paginate($limit);

        $categories->getCollection()->transform(function ($category) {
            $category->name = trans("categories.{$category->name}");
            $category->picture = "https://bozecommerce.sirv.com/category/" . $category->picture;
            return $category;
        });

        // Get the next page URL
        $nextPageUrl = $categories->nextPageUrl();

        return response()->json([
            "Category" => $categories->items(),
            "nextPageUrl" => $nextPageUrl,
            "Status" => 200,
        ]);
    }

    public function getSubCategory($id, $limit)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                "message" => "Category not found",
                "Status" => 404,
            ]);
        }

        // Retrieve subcategories with pagination
        $subCategories = $category->subCategories()
            ->select('id', 'name', 'picture', 'category_id')
            ->paginate($limit);

        $subCategories->getCollection()->transform(function ($subCategory) {
            $subCategory->name = trans("sub_categories.{$subCategory->name}");
            $subCategory->picture = "https://bozecommerce.sirv.com/subCategory/" . $subCategory->picture;
            return $subCategory;
        });

        // Get the next page URL
        $nextPageUrl = $subCategories->nextPageUrl();

        return response()->json([
            "Sub_Category" => $subCategories->items(),
            "nextPageUrl" => $nextPageUrl,
            "Status" => 200,
        ]);
    }
}
