<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Locale>
 */
class LocaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $locale = $this->faker->unique()->randomElement(['en', 'fr', 'es', 'de', 'it', 'pt', 'ar', 'zh', 'ru', 'ja']);

        return [
            'code' => $locale,  // e.g. en, fr
            'name' => ucfirst($locale), // e.g. En, Fr
        ];
    }
}
