<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuTranslation extends Model
{
    use HasFactory;

    protected $table = 'menu_translations';

    protected $fillable = [
        'menu_id',
        'locale',
        'name',
    ];

    /**
     * Get parent menu item.
     */
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }
}
