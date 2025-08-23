<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\Translation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TranslationSeeder extends Seeder
{
    public function run(int $total = 100000, int $chunk = 500): void
    {

        $tags = Tag::all();

        // echo "\n  Flushing translations and taggables tables...";
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // DB::table('translations')->truncate();
        // DB::table('taggables')->truncate();
        // DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        // echo "\n  Translations and taggables cleared successfully.";

        echo "\n  Starting seeder...\n";

        $start = microtime(true);

        DB::transaction(function () use ($chunk, $total, $tags) {
            Translation::withoutEvents(function () use ($chunk, $total, $tags) {
                $translationsToInsert = [];
                $pivotToInsert = [];
                $currentId = 1000000;
                $processed = 0;

                for ($i = 0; $i < $total; $i += $chunk) {
                    $innerStart = microtime(true);
                    $translationsToInsert = [];
                    $pivotToInsert = [];

                    Translation::factory()
                        ->count($chunk)
                        ->make()
                        ->each(function ($translation) use ($tags, &$translationsToInsert, &$pivotToInsert, &$currentId) {
                            // $translation->locale_id = $locales->random()->id;
                            $translation->id = $currentId;

                            $randomTags = $tags->random(rand(1, 3));
                            foreach ($randomTags as $tag) {
                                // Correctly format for a polymorphic pivot table
                                $pivotToInsert[] = [
                                    'tag_id' => $tag->id,
                                    'taggable_id' => $translation->id,
                                    'taggable_type' => Translation::class, // The class name of the related model
                                ];
                            }

                            $translationsToInsert[] = $translation->toArray();
                            $currentId++;
                        });

                    DB::table('translations')->insert($translationsToInsert);
                    DB::table('taggables')->insert($pivotToInsert);

                    $processed += count($translationsToInsert);
                    echo "  Seeded {$processed} translations so far...\n";

                    $time = round(microtime(true) - $innerStart, 2);
                    echo "  Time taken for this chunk: {$time}s\n";
                }
            });
        });

        $time = round(microtime(true) - $start, 2);
        echo "\n  Seeding finished in {$time}s!";
    }
}
