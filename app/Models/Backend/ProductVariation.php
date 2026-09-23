<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Size;

class ProductVariation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'product_variations';

    protected $fillable = [
        'product_id',
        'color_id',
        'size_id',
        'sku',
        'color_price',
        'size_price',
        'stock',
        'image',
        'status',
    ];

    protected $casts = [
        'status'      => 'boolean',
        'color_price' => 'decimal:2',
        'size_price'  => 'decimal:2',
        'stock'       => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function color()
    {
        return $this->belongsTo(Color::class, 'color_id');
    }

    public function size()
    {
        return $this->belongsTo(Size::class, 'size_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'variation_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'variation_id');
    }

    public function stockInDetails()
    {
        return $this->hasMany(StockInDetail::class, 'variation_id');
    }

    /**
     * Compute effective price for this variation:
     * Product base_price + color_price + size_price
     */
    public function getEffectivePriceAttribute(): float
    {
        $base = $this->product ? (float) $this->product->base_price : 0.0;
        return $base + (float) $this->color_price + (float) $this->size_price;
    }
}
