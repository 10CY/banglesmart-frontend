<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'short_description',
        'description',
        'mrp',
        'selling_price',
        'set_quantity',
        'featured',
        'best_seller',
        'new_arrival',
        'status',
        'seo_title',
        'seo_description',
    ];

    protected $casts = [
        'mrp' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'set_quantity' => 'integer',
        'featured' => 'boolean',
        'best_seller' => 'boolean',
        'new_arrival' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)
            ->where('is_primary', true);
    }
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
    public function movements()
    {
        return $this->hasMany(
            InventoryMovement::class,
            'product_variant_id'
        );
    }
    public function wishlistItems()
    {
        return $this->hasMany(
            WishlistItem::class
        );
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}