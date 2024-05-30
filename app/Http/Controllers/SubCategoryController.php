<?php

namespace App\Http\Controllers;

use App\Models\category;
use App\Models\SubCategory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;



class SubCategoryController extends Controller
{

    public function getProduct($id, $perPage)
    {
        $subCategory = SubCategory::find($id);

        if (!$subCategory) {
            return response()->json(
                [
                    "message" => "Sub Category not found",
                    "Status" => 404,
                ],
                404
            );
        }

        $products = $subCategory->products()
            ->select(
                'id',
                'name',
                'description',
                'picture',
                'quantity',
                'price',
                'calories',
                'sub_category_id'
            )
            ->paginate($perPage);

        $nextPageUrl = $products->nextPageUrl();

        $transformedProducts = $products->getCollection()->transform(function ($product) {
            $product->name = trans("products.name.{$product->name}");
            $product->description = trans("products.description.{$product->description}");
            $product->picture = "https://bozecommerce.sirv.com/product/" . $product->picture;
            return $product;
        });

        return response()->json(
            [
                "Product" => $transformedProducts,
                "next_page_url" => $nextPageUrl,
                "Status" => 200,
            ],
            200
        );
    }

    public function getProductCategory($id, $perPage)
    {
        $category = Category::findOrFail($id);

        $products = $category->subCategories()
            ->with(['products'])
            ->get()
            ->pluck('products')
            ->collapse();

        $products->transform(function ($product) {
            $product->name = trans("products.name.{$product->name}");
            $product->description = trans("products.description.{$product->description}");
            $product->picture = Storage::url('product/' . $product->picture);
            return $product;
        });

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentPageItems = $products->slice(($currentPage - 1) * $perPage, $perPage);
        $paginator = new LengthAwarePaginator($currentPageItems, $products->count(), $perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);

        $nextPageUrl = null;
        if ($paginator->hasMorePages()) {
            $nextPageUrl = $paginator->nextPageUrl();
        }

        return response()->json(
            [
                "Category Product" => $paginator->items(),
                "Next Page URL" => $nextPageUrl,
                "Status" => 200,
            ]
        );
    }
}
