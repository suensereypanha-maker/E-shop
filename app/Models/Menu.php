<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'menus';

    protected $fillable = [
        'parent_id',
        'name',
        'icon',
        'sort',
        'route',
        'badge',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];

    /**
     * Get parent menu item.
     */
    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    /**
     * Get child menu items.
     */
    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('sort', 'asc');
    }

    /**
     * Relational translations from menu_translations table.
     */
    public function translations()
    {
        return $this->hasMany(MenuTranslation::class, 'menu_id');
    }

    /**
     * Scope for root menu items.
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id')->orderBy('sort', 'asc');
    }

    /**
     * Scope for active menu items.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get translated name with fallback.
     */
    public function getTranslatedName($locale = null)
    {
        $locale = $locale ?: app()->getLocale();

        // Check relational translations collection
        if ($this->relationLoaded('translations')) {
            $collection = $this->getRelation('translations');
            if ($collection) {
                $trans = $collection->firstWhere('locale', $locale);
                if ($trans && !empty($trans->name)) {
                    return $trans->name;
                }
            }
        } else {
            $trans = $this->translations()->where('locale', $locale)->first();
            if ($trans && !empty($trans->name)) {
                return $trans->name;
            }
        }

        return $this->name;
    }

    /**
     * Get translations formatted as key-value array [ 'en' => ['name' => '...'], ... ]
     */
    public function getTranslationsArray(): array
    {
        $result = [];
        $collection = $this->relationLoaded('translations')
            ? $this->getRelation('translations')
            : $this->translations()->get();

        if ($collection) {
            foreach ($collection as $t) {
                $result[$t->locale] = ['name' => $t->name];
            }
        }

        if (empty($result)) {
            $result['en'] = ['name' => $this->name];
        }

        return $result;
    }

    /**
     * Check if item has children.
     */
    public function hasChildren()
    {
        return $this->children->isNotEmpty();
    }
}
