<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'suppliers';

    protected $fillable = [
        'name',
        'code',
        'company_name',
        'phone',
        'email',
        'address',
        'contact_person',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

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

    /**
     * Auto-generate a formatted unique supplier code (e.g. SUP-0001).
     */
    public static function generateCode(): string
    {
        $lastSupplier = static::withTrashed()
            ->whereNotNull('code')
            ->where('code', 'like', 'SUP-%')
            ->orderBy('id', 'desc')
            ->first();

        $nextNum = 1;
        if ($lastSupplier && preg_match('/SUP-(\d+)/', $lastSupplier->code, $matches)) {
            $nextNum = intval($matches[1]) + 1;
        }

        $code = 'SUP-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
        while (static::withTrashed()->where('code', $code)->exists()) {
            $nextNum++;
            $code = 'SUP-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
        }

        return $code;
    }
}
