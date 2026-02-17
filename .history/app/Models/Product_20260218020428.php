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
    // For attributes
    public function attributeValues()
    {
        return $this->belongsToMany(
            \App\Models\AttributeValue::class,
            'product_attribute_values',
            'product_id',
            'attribute_value_id'
        )->withPivot('attribute_id')->withTimestamps();
    }
}
