<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'product_type',
        'description',
        'short_description',
        'regular_price',
        'sale_price',
        'sku',
        'barcode',
        'stock',
        'shipping_price',
        'category_id',
        'brand_id',
        'featured_image',
        'download_file',
        'is_active'
    ];

    public function gallery()
    {
        return $this->hasMany(ProductGallery::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
    // For Reviews
    public function reviews()
    {
        return $this->hasMany(\App\Models\Review::class);
    }
    public function stockMovements()
    {
        return $this->hasMany(\App\Models\StockMovement::class);
    }
}
