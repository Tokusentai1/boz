<?php

namespace App\Http\Controllers;

use App\Models\category;
use App\Models\SubCategory;
use Illuminate\Support\Facades\Storage;


class SubCategoryController extends Controller
{

    public function getProduct($id, $limit)
    {
        $subCategory = SubCategory::find($id);

        if (!$subCategory) {
            return response()->json(
                [
                    "message" => "Sub Category not found",
                    "Status" => 404,
                ],
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
            ->limit($limit)
            ->get();

        foreach ($products as $product) {
            $product->name = trans("products.name.{$product->name}");
            $product->description = trans("products.description.{$product->description}");
            $product->picture = Storage::url('product/' . $product->picture);
        }

        return response()->json(
            [
                "Product" => $products,
                "Status" => 200,
            ]
        );
    }


    public function getProductCategory($id, $limit)
    {
        $category = Category::findOrFail($id);

        $products = $category->subCategories()
            ->with(['products' => function ($query) use ($limit) {
                $query->select(
                    'id',
                    'name',
                    'description',
                    'picture',
                    'quantity',
                    'price',
                    'calories',
                    'sub_category_id'
                )->limit($limit);
            }])
            ->get()
            ->pluck('products')
            ->collapse();

        $products->transform(function ($product) {
            $product->name = trans("products.name.{$product->name}");
            $product->description = trans("products.description.{$product->description}");
            $product->picture = Storage::url('product/' . $product->picture);
            return $product;
        });

        return response()->json(
            [
                "Category Product" => $products,
                "Status" => 200,
            ]
        );
    }
}
