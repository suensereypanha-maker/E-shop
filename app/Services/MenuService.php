<?php

namespace App\Services;

use App\Models\Menu;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class MenuService
{
    /**
     * Get menu tree from database with eager loaded translations.
     *
     * @return \Illuminate\Support\Collection|array
     */
    public function getMenuTree()
    {
        try {
            $menus = Menu::root()
                ->active()
                ->with([
                    'translations',
                    'children' => function ($query) {
                        $query->active()->with('translations')->orderBy('sort', 'asc');
                    }
                ])
                ->orderBy('sort', 'asc')
                ->get();

            if ($menus->isNotEmpty()) {
                return $menus;
            }
        } catch (\Throwable $e) {
            Log::warning('Failed loading menus from DB, falling back to JSON: ' . $e->getMessage());
        }

        return $this->getMenusFromJson();
    }

    /**
     * Read and decode menu.json.
     *
     * @return array
     */
    public function getMenusFromJson(): array
    {
        $path = database_path('data/menu.json');
        if (File::exists($path)) {
            $content = File::get($path);
            $data = json_decode($content, true);
            if (is_array($data)) {
                return $data;
            }
        }
        return [];
    }

    /**
     * Get raw JSON string.
     *
     * @return string
     */
    public function getRawJson(): string
    {
        $path = database_path('data/menu.json');
        if (File::exists($path)) {
            return File::get($path);
        }
        return json_encode([]);
    }
}
