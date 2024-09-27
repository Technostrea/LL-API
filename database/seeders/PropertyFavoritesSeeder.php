<?php

namespace Database\Seeders;

use App\Models\PropertyFavorites;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PropertyFavoritesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PropertyFavorites::factory(100)->create();
    }
}
