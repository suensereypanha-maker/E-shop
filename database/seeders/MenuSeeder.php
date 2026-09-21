<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\Menu;
use App\Models\MenuTranslation;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $jsonPath = database_path('data/menu.json');

        if (!File::exists($jsonPath)) {
            $this->command->error("File not found: {$jsonPath}");
            return;
        }

        $json = File::get($jsonPath);
        $menus = json_decode($json, true);

        if (!is_array($menus)) {
            $this->command->error("Invalid JSON format in {$jsonPath}");
            return;
        }

        // Temporarily disable foreign keys to safely clean tables
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        MenuTranslation::truncate();
        Menu::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $menuCount = 0;
        $translationCount = 0;

        foreach ($menus as $item) {
            $parent = Menu::create([
                'parent_id' => null,
                'name' => $item['name'],
                'icon' => $item['icon'] ?? null,
                'sort' => $item['sort'] ?? 0,
                'route' => $item['route'] ?? null,
                'badge' => $item['badge'] ?? null,
                'is_active' => true,
            ]);
            $menuCount++;

            // Seed translations into menu_translations table
            if (!empty($item['translations']) && is_array($item['translations'])) {
                foreach ($item['translations'] as $locale => $trans) {
                    MenuTranslation::create([
                        'menu_id' => $parent->id,
                        'locale' => $locale,
                        'name' => $trans['name'] ?? $item['name'],
                    ]);
                    $translationCount++;
                }
            } else {
                MenuTranslation::create([
                    'menu_id' => $parent->id,
                    'locale' => 'en',
                    'name' => $item['name'],
                ]);
                $translationCount++;
            }

            // Seed children and their translations
            if (!empty($item['children']) && is_array($item['children'])) {
                foreach ($item['children'] as $child) {
                    $childMenu = Menu::create([
                        'parent_id' => $parent->id,
                        'name' => $child['name'],
                        'icon' => $child['icon'] ?? null,
                        'sort' => $child['sort'] ?? 0,
                        'route' => $child['route'] ?? null,
                        'badge' => $child['badge'] ?? null,
                        'is_active' => true,
                    ]);
                    $menuCount++;

                    if (!empty($child['translations']) && is_array($child['translations'])) {
                        foreach ($child['translations'] as $locale => $trans) {
                            MenuTranslation::create([
                                'menu_id' => $childMenu->id,
                                'locale' => $locale,
                                'name' => $trans['name'] ?? $child['name'],
                            ]);
                            $translationCount++;
                        }
                    } else {
                        MenuTranslation::create([
                            'menu_id' => $childMenu->id,
                            'locale' => 'en',
                            'name' => $child['name'],
                        ]);
                        $translationCount++;
                    }
                }
            }
        }

        $this->command->info("Successfully seeded {$menuCount} menus and {$translationCount} translations into menu_translations table!");
    }
}
