<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\Menu;

class MenuSeeder extends Seeder
{
    public function run()
    {
        Menu::truncate();

        $json = File::get(database_path('data/menus.json'));
        $menus = json_decode($json, true);

        foreach ($menus as $menuData) {
            $this->createMenu($menuData);
        }
    }

    private function createMenu(array $menuData)
    {
        $menu = Menu::create([
            'title' => $menuData['title'],
            'url' => $menuData['url'],
            'icon' => $menuData['icon'],
            'order' => $menuData['order'],
            'is_active' => $menuData['is_active'],
            'parent_id' => $menuData['parent_id']
        ]);

        // Insert child menus if present
        if (!empty($menuData['children'])) {
            foreach ($menuData['children'] as $child) {
                Menu::create([
                    'title' => $child['title'],
                    'url' => $child['url'],
                    'icon' => $child['icon'],
                    'order' => $child['order'],
                    'is_active' => $child['is_active'],
                    'parent_id' => $menu->id  // Set the parent_id to the ID of the parent menu
                ]);
            }
        }
    }
}

