<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'role_id',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The role that belongs to this user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Get active role slug.
     */
    public function getRoleSlugAttribute(): ?string
    {
        return $this->role?->slug;
    }

    /**
     * Check if user has specific role or one of given roles.
     * Usage: $user->hasRole('admin') or $user->hasRole('admin', 'manager') or $user->hasRole(['admin', 'manager'])
     */
    public function hasRole(...$roles): bool
    {
        if (empty($roles)) {
            return false;
        }

        $flattened = [];
        foreach ($roles as $r) {
            if (is_array($r)) {
                $flattened = array_merge($flattened, $r);
            } else {
                $flattened[] = $r;
            }
        }

        $currentSlug = $this->role_slug;
        return in_array($currentSlug, $flattened, true);
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(Role::ROLE_ADMIN);
    }

    /**
     * Check if user is a manager.
     */
    public function isManager(): bool
    {
        return $this->hasRole(Role::ROLE_MANAGER);
    }

    /**
     * Check if user is a cashier.
     */
    public function isCashier(): bool
    {
        return $this->hasRole(Role::ROLE_CASHIER);
    }

    /**
     * Check if user is a standard user/customer.
     */
    public function isUser(): bool
    {
        return $this->hasRole(Role::ROLE_USER);
    }

    /**
     * Get displayable role title.
     */
    public function getRoleTitleAttribute(): string
    {
        return $this->role?->name ?? 'Guest User';
    }

    /**
     * Get styling badge attributes for this role.
     */
    public function getRoleBadgeAttribute(): array
    {
        if ($this->role?->badge) {
            return $this->role->badge;
        }

        return [
            'title' => 'Guest User',
            'bg' => 'bg-slate-100 text-slate-700 border-slate-200',
            'dot' => 'bg-slate-400',
            'icon' => 'bi-person',
        ];
    }
}
