<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class StockAdjustment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'stock_adjustments';

    protected $fillable = [
        'reference_no',
        'date',
        'reason',
        'total_items',
        'total_qty_adjusted',
        'total_cost_impact',
        'note',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date'               => 'date',
        'total_items'        => 'integer',
        'total_qty_adjusted' => 'integer',
        'total_cost_impact'  => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(StockAdjustmentDetail::class, 'stock_adjustment_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function movements()
    {
        return $this->morphMany(StockMovement::class, 'reference')->orderBy('id', 'asc');
    }

    /**
     * Auto-generate a formatted unique reference code (e.g. ADJ-20260922-0001).
     */
    public static function generateReferenceNo(): string
    {
        $prefix = 'ADJ-' . date('Ymd') . '-';
        $latest = self::withTrashed()
            ->where('reference_no', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if (!$latest) {
            return $prefix . '0001';
        }

        $lastSeq = (int) substr($latest->reference_no, -4);
        return $prefix . str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Human-readable label for adjustment reason.
     */
    public function getReasonLabelAttribute(): string
    {
        return match ($this->reason) {
            'damage'         => 'Damaged Goods',
            'lost_theft'      => 'Lost / Theft',
            'count_mismatch' => 'Physical Count Mismatch',
            'expired'        => 'Expired Batch',
            'found_stock'    => 'Found Inventory (+)',
            default          => ucfirst(str_replace('_', ' ', $this->reason ?: 'Other')),
        };
    }
}
