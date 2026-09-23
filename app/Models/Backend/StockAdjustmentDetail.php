<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAdjustmentDetail extends Model
{
    use HasFactory;

    protected $table = 'stock_adjustment_details';

    protected $fillable = [
        'stock_adjustment_id',
        'product_id',
        'variation_id',
        'type',
        'current_stock',
        'quantity',
        'final_stock',
        'unit_cost',
        'total_cost',
        'reason',
    ];

    protected $casts = [
        'current_stock' => 'integer',
        'quantity'      => 'integer',
        'final_stock'   => 'integer',
        'unit_cost'     => 'decimal:2',
        'total_cost'    => 'decimal:2',
    ];

    public function adjustment()
    {
        return $this->belongsTo(StockAdjustment::class, 'stock_adjustment_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'variation_id');
    }
}
