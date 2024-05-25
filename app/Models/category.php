<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'picture'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function subCategories()
    {
        return $this->hasMany(SubCategory::class);
    }
}
