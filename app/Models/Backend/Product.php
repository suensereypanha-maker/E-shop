<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;



class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'slug',
        'sku',
        'description',
        'cost_price',
        'base_price',
        'sale_price',
        'wholesale_price',
        'image',
        'status',
        'is_featured',
        'is_new',
        'is_bundle',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'status'          => 'boolean',
        'is_featured'     => 'boolean',
        'is_new'          => 'boolean',
        'is_bundle'       => 'boolean',
        'cost_price'      => 'decimal:2',
        'base_price'      => 'decimal:2',
        'sale_price'      => 'decimal:2',
        'wholesale_price' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brands::class, 'brand_id');
    }

    public function variations()
    {
        return $this->hasMany(ProductVariation::class, 'product_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id')->orderBy('sort_order');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'product_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

  
    public function getTotalStockAttribute(): int
    {
        return (int) $this->variations()->sum('stock');
    }
}
