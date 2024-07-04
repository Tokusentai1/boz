<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'picture',
        'quantity',
        'price',
        'calories',
        'sub_category_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function carts()
    {
        return $this->belongsToMany(Cart::class)->withPivot('quantity');
    }

    public function wishlists()
    {
        return $this->belongsToMany(wishlist::class);
    }
}
