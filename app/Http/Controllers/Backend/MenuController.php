<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\MenuService;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    protected $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    /**
     * Get menu tree as JSON with translations from menu_translations table.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getJson(Request $request)
    {
        $locale = $request->query('lang', null);

        $menus = Menu::root()
            ->active()
            ->with([
                'translations',
                'children' => function ($q) {
                    $q->active()->with('translations')->orderBy('sort', 'asc');
                }
            ])
            ->orderBy('sort', 'asc')
            ->get();

        if ($menus->isEmpty()) {
            $raw = json_decode($this->menuService->getRawJson(), true);
            return response()->json($raw, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        $formatted = $menus->map(function ($root) use ($locale) {
            return [
                'id' => $root->id,
                'name' => $locale ? $root->getTranslatedName($locale) : $root->name,
                'icon' => $root->icon,
                'sort' => $root->sort,
                'route' => $root->route,
                'badge' => $root->badge,
                'children' => $root->children->map(function ($child) use ($locale) {
                    return [
                        'id' => $child->id,
                        'name' => $locale ? $child->getTranslatedName($locale) : $child->name,
                        'icon' => $child->icon,
                        'sort' => $child->sort,
                        'route' => $child->route,
                        'badge' => $child->badge,
                        'translations' => $child->getTranslationsArray(),
                    ];
                }),
                'translations' => $root->getTranslationsArray(),
            ];
        });

        return response()->json($formatted, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Download menu.json file directly.
     */
    public function downloadJson()
    {
        $filePath = database_path('data/menu.json');
        if (file_exists($filePath)) {
            return response()->download($filePath, 'menu.json', [
                'Content-Type' => 'application/json',
            ]);
        }

        return response()->json(['error' => 'File not found'], 404);
    }
}
