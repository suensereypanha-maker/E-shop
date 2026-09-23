<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOutDetail extends Model
{
    use HasFactory;

    protected $table = 'stock_out_details';

    protected $fillable = [
        'stock_out_id',
        'product_id',
        'variation_id',
        'quantity',
        'stock_before',
        'stock_after',
        'unit_cost',
        'total_cost',
        'reason',
    ];

    protected $casts = [
        'quantity'     => 'integer',
        'stock_before' => 'integer',
        'stock_after'  => 'integer',
        'unit_cost'    => 'decimal:2',
        'total_cost'   => 'decimal:2',
    ];

    public function stockOut()
    {
        return $this->belongsTo(StockOut::class, 'stock_out_id');
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
