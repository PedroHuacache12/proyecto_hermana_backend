<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Catalog extends Model
{
    protected $fillable = [
        'user_id', 'name', 'slug', 'description',
        'logo_url', 'brand_name', 'published', 'starts_at', 'ends_at',
    ];

    protected $casts = [
        'published' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'catalog_products')->withPivot('order')->withTimestamps()->orderBy('catalog_products.order');
    }

    public function visitorSessions()
    {
        return $this->hasMany(VisitorSession::class);
    }

    public function isActive(): bool
    {
        if (!$this->published) return false;
        $now = now();
        if ($this->starts_at && $now->lt($this->starts_at)) return false;
        if ($this->ends_at && $now->gt($this->ends_at)) return false;
        return true;
    }
}
