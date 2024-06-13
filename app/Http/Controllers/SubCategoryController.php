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
                "message" => "Sub Category not found",
                "Status" => 404,
            ], 404);
        }

        $query = $subCategory->products()
            ->select('id', 'name', 'description', 'picture', 'quantity', 'price', 'calories', 'sub_category_id');

        // Check if $index and $limit are provided for pagination
        if ($index !== null && $limit !== null) {
            $query->skip($index)->take($limit);
        }

        $products = $query->get();

        // Transform the products
        $transformedProducts = $products->map(function ($product) {
            $product->name = trans("products.name.{$product->name}");
            $product->description = trans("products.description.{$product->description}");
            $product->picture = "https://bozecommerce.sirv.com/product/" . $product->picture;
            return $product;
        });

        // Determine if there are more products to fetch
        $totalProducts = $subCategory->products()->count();
        $hasMore = ($index !== null && $limit !== null) ? ($index + $limit) < $totalProducts : false;

        return response()->json([
            "Product" => $transformedProducts,
            "hasMore" => $hasMore,
            "Status" => 200,
        ], 200);
    }

    public function getProductCategory($id, $index = null, $limit = null)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                "message" => "Category not found",
                "Status" => 404,
            ], 404);
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
        $transformedProducts = $products->map(function ($product) {
            $product->name = trans("products.name.{$product->name}");
            $product->description = trans("products.description.{$product->description}");
            $product->picture = "https://bozecommerce.sirv.com/product/" . $product->picture;
            return $product;
        });

        // Determine if there are more products to fetch
        $totalProducts = $category->subCategories()->with('products')->get()->pluck('products')->collapse()->count();
        $hasMore = ($index !== null && $limit !== null) ? ($index + $limit) < $totalProducts : false;

        return response()->json([
            "Category Product" => $transformedProducts,
            "hasMore" => $hasMore,
            "Status" => 200,
        ], 200);
    }
}
