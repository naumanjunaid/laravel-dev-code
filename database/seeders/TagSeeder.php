<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tag::insert([
            [
                'name' => 'mobile',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'desktop',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
