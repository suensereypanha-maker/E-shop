<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class StockMovement extends Model
{
    use HasFactory;

    protected $table = 'stock_movements';

    protected $fillable = [
        'product_id',
        'variation_id',
        'type',
        'reference_type',
        'reference_id',
        'quantity',
        'stock_before',
        'stock_after',
        'unit_cost',
        'note',
        'created_by',
    ];

    protected $casts = [
        'quantity'     => 'integer',
        'stock_before' => 'integer',
        'stock_after'  => 'integer',
        'unit_cost'    => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'variation_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reference()
    {
        return $this->morphTo(__FUNCTION__, 'reference_type', 'reference_id');
    }

    public function getReferenceNoAttribute(): string
    {
        if ($this->reference && !empty($this->reference->reference_no)) {
            return $this->reference->reference_no;
        }

        if (preg_match('/(\d{8}-\d{3,4})/', $this->note ?? '', $matches)) {
            return $matches[1];
        }

        return '-';
    }

    public function getReasonLabelAttribute(): string
    {
        if (str_contains($this->note ?? '', 'Reversed cancellation') || str_contains($this->note ?? '', 'cancelled')) {
            return 'Cancellation Reversal';
        }

        if ($this->reference && method_exists($this->reference, 'getReasonLabelAttribute')) {
            return $this->reference->reason_label;
        }

        if ($this->reference_type === StockIn::class || $this->type === 'stock_in') {
            return 'Stock In Replenishment';
        }

        if (preg_match('/\(([^)]+)\)$/', $this->note ?? '', $matches)) {
            return $matches[1];
        }

        return match ($this->type) {
            'stock_in'   => 'Stock In Replenishment',
            'stock_out'  => 'Stock Dispatch',
            'adjustment' => 'Inventory Adjustment',
            'sale'       => 'Sale / Order Dispatch',
            'return'     => 'Customer Return',
            'transfer'   => 'Warehouse Transfer',
            default      => ucfirst(str_replace('_', ' ', $this->type ?: 'General Movement')),
        };
    }

    public function getReferenceUrlAttribute(): ?string
    {
        if (!$this->reference_id) {
            return null;
        }

        return match ($this->reference_type) {
            StockIn::class         => route('backend.stock-ins.show', $this->reference_id),
            StockOut::class        => route('backend.stock-outs.show', $this->reference_id),
            StockAdjustment::class => route('backend.stock-adjustments.show', $this->reference_id),
            default                => null,
        };
    }
}
