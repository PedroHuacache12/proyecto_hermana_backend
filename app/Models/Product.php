<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'user_id', 'name', 'brand', 'description', 'price',
        'benefits', 'preparation', 'images', 'background_image', 'ingredients', 'active',
    ];

    protected $casts = [
        'benefits' => 'array',
        'preparation' => 'array',
        'images' => 'array',
        'ingredients' => 'array',
        'active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function catalogs()
    {
        return $this->belongsToMany(Catalog::class, 'catalog_products')->withPivot('order')->withTimestamps();
    }
}
