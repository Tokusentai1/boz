<?php

namespace App\Http\Controllers;

use App\Models\category;
use App\Models\SubCategory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;



class SubCategoryController extends Controller
{

    public function getProduct($id, $index = null, $limit = null)
    {
        $subCategory = SubCategory::find($id);

        if (!$subCategory) {
            return response()->json([
                "success" => false,
                "statusCode" => 404,
                "error" => "Sub Category not found",
                "result" => null,
            ]);
        }

        $query = $subCategory->products()
            ->select('id', 'name', 'description', 'picture', 'quantity', 'price', 'calories', 'sub_category_id');

        // Check if $index and $limit are provided for pagination
        if ($index !== null && $limit !== null) {
            $query->skip($index)->take($limit);
        }

        $products = $query->get();

        // Transform the products
        $transformedProducts = $products->map(function ($product) use ($index, $limit, $subCategory) {
            $product->name = trans("products.name.{$product->name}");
            $product->description = trans("products.description.{$product->description}");
            $product->picture = "https://bozecommerce.sirv.com/product/" . $product->picture;
            $product->hasMore = ($index !== null && $limit !== null) ? ($index + $limit) < $subCategory->products()->count() : false;
            return $product;
        });

        return response()->json([
            "success" => true,
            "statusCode" => 200,
            "error" => null,
            "result" => $transformedProducts,
        ]);
    }

    public function getProductCategory($id, $index = null, $limit = null)
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

        // Get all products related to the category's subcategories
        $products = $category->subCategories()
            ->with('products')
            ->get()
            ->pluck('products')
            ->collapse();

        // Check if index and limit are provided for pagination
        if ($index !== null && $limit !== null) {
            $products = $products->slice($index, $limit);
        }

        // Transform the products
        $transformedProducts = $products->map(function ($product) use ($index, $limit, $category) {
            $product->name = trans("products.name.{$product->name}");
            $product->description = trans("products.description.{$product->description}");
            $product->picture = "https://bozecommerce.sirv.com/product/" . $product->picture;
            $product->hasMore = ($index !== null && $limit !== null) ? ($index + $limit) < $category->subCategories()->with('products')->get()->pluck('products')->collapse()->count() : false;
            return $product;
        });

        return response()->json([
            "success" => true,
            "statusCode" => 200,
            "error" => null,
            "result" => $transformedProducts,
        ]);
    }
}
