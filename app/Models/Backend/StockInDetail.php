<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockInDetail extends Model
{
    use HasFactory;

    protected $table = 'stock_in_details';

    protected $fillable = [
        'stock_in_id',
        'product_id',
        'variation_id',
        'quantity',
        'stock_before',
        'stock_after',
        'unit_cost',
        'total_cost',
        'batch_no',
        'expiry_date',
    ];

    protected $casts = [
        'quantity'     => 'integer',
        'stock_before' => 'integer',
        'stock_after'  => 'integer',
        'unit_cost'    => 'decimal:2',
        'total_cost'   => 'decimal:2',
        'expiry_date'  => 'date',
    ];

    public function stockIn()
    {
        return $this->belongsTo(StockIn::class, 'stock_in_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'variation_id');
    }

    public function getMovementAttribute()
    {
        return StockMovement::where('reference_type', StockIn::class)
            ->where('reference_id', $this->stock_in_id)
            ->where('variation_id', $this->variation_id)
            ->where('type', 'stock_in')
            ->first();
    }
}
