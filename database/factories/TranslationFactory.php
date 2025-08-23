<?php

namespace Database\Factories;

use App\Models\Locale;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Translation>
 */
class TranslationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $locales = ['en' => 'English', 'fr' => 'French', 'es' => 'Spanish'];
        foreach ($locales as $code => $name) {
            Locale::firstOrCreate([
                'code' => $code,
                'name' => $name,
            ]);
        }

        $tags = ['mobile', 'desktop', 'web'];
        foreach ($tags as $name) {
            Tag::firstOrCreate(['name' => $name]);
        }

        static $counter = 1;

        $localeId = Locale::inRandomOrder()->first()->id;
        $key = Str::slug($this->faker->words(2, true).$counter, '_');
        $content = $this->faker->words(4, true).' '.$counter;

        $counter++;

        return [
            'locale_id' => $localeId,
            'key' => $key,
            'content' => $content,
        ];
    }
}
