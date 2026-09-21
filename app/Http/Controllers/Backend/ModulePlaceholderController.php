<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\MenuService;
use Illuminate\Http\Request;

class ModulePlaceholderController extends Controller
{
    protected $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    /**
     * Show module placeholder view.
     */
    public function show(Request $request, string $module)
    {
        $menus = $this->menuService->getMenuTree();
        $title = ucwords(str_replace(['-', '_', '.'], ' ', $module));

        return view('backend.placeholder', [
            'menus' => $menus,
            'title' => $title,
            'moduleKey' => $module,
        ]);
    }
}
