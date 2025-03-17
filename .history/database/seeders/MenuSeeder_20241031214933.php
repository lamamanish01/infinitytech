<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\File;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Menu::truncate();

        $json = File::get(database_path('data/menus.json'));
        $menus = json_decode($json, true);
    }

        public function createMenu()
        {
            foreach ($menus as $menu)
            {
                Menu::create([
                    'title' => $menu->title,
                    'url' => $menu->url,
                    'icon' => $menu->icon,
                    'parent_id' => $menu->parent_id,
                    'order' => $menu->order,
                    'role' => $menu->role
                ]);

                if (!empty($menus['children']))
                {
                    foreach ($menus['children'] as $child)
                    {
                        $this->createMenu($child);
                    }
                }
            }
        }
}
