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

    public function category()
    {
        return $this->belongsTo(\App\Models\Category::class, 'category_id');
    }

    public function brand()
    {
        return $this->belongsTo(\App\Models\Brand::class, 'brand_id');
    }

    public function gallery()
    {
        return $this->hasMany(\App\Models\ProductGallery::class);
    }

    public function variants()
    {
        return $this->hasMany(\App\Models\ProductVariant::class);
    }

    // ✅ if you created pivot table product_attribute_values
    public function attributeValues()
    {
        return $this->belongsToMany(
            \App\Models\AttributeValue::class,
            'product_attribute_values',
            'product_id',
            'attribute_value_id'
        )->withPivot('attribute_id')->withTimestamps();
    }
     public function stockMovements(): HasMany
    {
        return $this->hasMany(\App\Models\StockMovement::class);
        // If your FK is not product_id, use:
        // return $this->hasMany(StockMovement::class, 'product_id');
    }
}
