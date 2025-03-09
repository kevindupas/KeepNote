<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $systemCategories = [
            [
                'name' => 'Travail',
                'color' => '#FF5733',
                'is_system' => true,
            ],
            [
                'name' => 'Personnel',
                'color' => '#33FF57',
                'is_system' => true,
            ],
            [
                'name' => 'Urgent',
                'color' => '#FF3357',
                'is_system' => true,
            ],
            [
                'name' => 'Idées',
                'color' => '#5733FF',
                'is_system' => true,
            ],
            [
                'name' => 'Projet',
                'color' => '#33B5FF',
                'is_system' => true,
            ],
        ];

        foreach ($systemCategories as $category) {
            Category::create($category);
        }
    }
}
