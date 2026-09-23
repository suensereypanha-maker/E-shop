<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Backend\Product;
use App\Models\Backend\ProductVariation;
use App\Models\Backend\StockInDetail;
use App\Models\Backend\Supplier;

class StockIn extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'stock_ins';

    protected $fillable = [
        'reference_no',
        'supplier_invoice_no',
        'supplier_id',
        'received_date',
        'status',
        'total_quantity',
        'total_cost',
        'note',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'received_date'  => 'date',
        'total_quantity' => 'integer',
        'total_cost'     => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(StockInDetail::class, 'stock_in_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
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

    protected static function booted()
    {
        static::creating(function ($stockIn) {
            if (empty($stockIn->reference_no)) {
                $stockIn->reference_no = static::generateReferenceNo($stockIn->received_date);
            }
            if (empty($stockIn->supplier_invoice_no)) {
                $stockIn->supplier_invoice_no = static::generateSupplierInvoiceNo($stockIn->reference_no, $stockIn->received_date);
            }
        });
    }

    /**
     * Auto-generate a formatted unique reference code (e.g. 20260923-0001).
     */
    public static function generateReferenceNo($date = null): string
    {
        $dateStr = $date ? date('Ymd', strtotime((string) $date)) : date('Ymd');
        $prefix = $dateStr . '-';
        $lastRecord = static::withTrashed()
            ->where('reference_no', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        $nextNum = 1;
        if ($lastRecord && preg_match('/-(\d+)$/', $lastRecord->reference_no, $matches)) {
            $nextNum = (int) $matches[1] + 1;
        }

        return $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Auto-generate a formatted supplier invoice code (e.g. INV-20260923-0001).
     */
    public static function generateSupplierInvoiceNo(?string $referenceNo = null, $date = null): string
    {
        if ($referenceNo) {
            return str_starts_with($referenceNo, 'INV-') ? $referenceNo : 'INV-' . $referenceNo;
        }

        return 'INV-' . static::generateReferenceNo($date);
    }
}
