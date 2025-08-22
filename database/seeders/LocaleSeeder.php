<?php

namespace Database\Seeders;

use App\Models\Locale;
use Illuminate\Database\Seeder;

class LocaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Locale::insert([
            [
                'code' => 'en',
                'name' => 'English',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'fr',
                'name' => 'French',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'es',
                'name' => 'Spanish',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
