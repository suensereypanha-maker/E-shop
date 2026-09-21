<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_CASHIER = 'cashier';
    public const ROLE_USER = 'user';

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    /**
     * Get all users that belong to this role.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Badge visual styling based on role slug.
     */
    public function getBadgeAttribute(): array
    {
        return match ($this->slug) {
            self::ROLE_ADMIN => [
                'title' => $this->name ?? 'Super Admin',
                'bg' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                'dot' => 'bg-indigo-500',
                'icon' => 'bi-shield-check',
            ],
            self::ROLE_MANAGER => [
                'title' => $this->name ?? 'Store Manager',
                'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'dot' => 'bg-emerald-500',
                'icon' => 'bi-person-badge',
            ],
            self::ROLE_CASHIER => [
                'title' => $this->name ?? 'POS Cashier',
                'bg' => 'bg-amber-50 text-amber-700 border-amber-200',
                'dot' => 'bg-amber-500',
                'icon' => 'bi-cash-coin',
            ],
            self::ROLE_USER => [
                'title' => $this->name ?? 'Customer',
                'bg' => 'bg-sky-50 text-sky-700 border-sky-200',
                'dot' => 'bg-sky-500',
                'icon' => 'bi-person',
            ],
            default => [
                'title' => $this->name ?? ucfirst($this->slug ?? 'User'),
                'bg' => 'bg-slate-100 text-slate-700 border-slate-200',
                'dot' => 'bg-slate-400',
                'icon' => 'bi-person',
            ],
        };
    }
}
