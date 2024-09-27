<?php

namespace Database\Seeders;

use App\Models\PropertyImages;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PropertyImagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PropertyImages::factory(100)->create();
    }
}
