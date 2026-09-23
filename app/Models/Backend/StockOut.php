<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class StockOut extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'stock_outs';

    protected $fillable = [
        'reference_no',
        'date',
        'reason',
        'recipient_name',
        'status',
        'total_quantity',
        'total_cost',
        'note',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'date'           => 'date',
        'total_quantity' => 'integer',
        'total_cost'     => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(StockOutDetail::class, 'stock_out_id');
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

    public function movements()
    {
        return $this->morphMany(StockMovement::class, 'reference')->orderBy('id', 'asc');
    }

    public function getReasonLabelAttribute(): string
    {
        return match ($this->reason) {
            'sale_dispatch'   => 'Sale / Dispatch',
            'damage_scrap'    => 'Damaged / Scrap',
            'internal_use'    => 'Internal / Store Use',
            'sample'          => 'Sample / Promotion',
            'return_supplier' => 'Return to Supplier',
            'expired'         => 'Expired Items',
            'loss_theft'      => 'Lost / Theft',
            default           => ucfirst(str_replace('_', ' ', $this->reason ?: 'Other')),
        };
    }

    /**
     * Auto-generate a formatted unique reference code (e.g. STK-OUT-20260922-0001).
     */
    public static function generateReferenceNo(): string
    {
        $prefix = date('Ymd') . '-';
        $lastRecord = static::withTrashed()
            ->where('reference_no', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        $nextNum = 1;
        if ($lastRecord && preg_match('/-(\d+)$/', $lastRecord->reference_no, $matches)) {
            $nextNum = (int) $matches[1] + 1;
        }

        return $prefix . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
    }
}
