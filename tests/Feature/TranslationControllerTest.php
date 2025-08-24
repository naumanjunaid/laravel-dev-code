<?php

namespace Tests\Feature;

use App\Models\Locale;
use App\Models\Tag;
use App\Models\Translation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TranslationControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_it_lists_all_translations()
    {
        $locale = Locale::factory()->create(['code' => $this->faker->unique()->locale]);
        $tag = Tag::factory()->create();
        $translation = Translation::factory()->create(['locale_id' => $locale->id]);
        $translation->tags()->attach($tag);

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->getJson('/api/translations')
            ->assertStatus(200)
            ->assertJsonFragment(['key' => $translation->key]);
    }

    public function test_it_filters_translations_by_locale_tag_key_and_content()
    {
        $locale = Locale::factory()->create(['code' => $this->faker->unique()->locale]);
        $localeCode = $locale->code;
        $locale2 = Locale::factory()->create(['code' => $this->faker->unique()->locale]);
        $tagMobile = Tag::factory()->create(['name' => 'mobile']);
        $tagWeb = Tag::factory()->create(['name' => 'web']);

        $t1 = Translation::factory()->create([
            'key' => 'checkout.title',
            'content' => 'Paiement',
            'locale_id' => $locale->id,
        ]);
        $t1->tags()->attach([$tagMobile->id]);

        $t2 = Translation::factory()->create([
            'key' => 'home.title',
            'content' => 'Payment',
            'locale_id' => $locale2->id,
        ]);
        $t2->tags()->attach([$tagWeb->id]);

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->getJson('/api/translations?locale=' . $localeCode . '&tag=' . $tagMobile->id . '&key=checkout&content=Paiement')
            ->assertStatus(200)
            ->assertJsonFragment(['key' => 'checkout.title'])
            ->assertJsonMissing(['key' => 'home.title']);
    }

    public function test_it_returns_nested_format_when_requested()
    {
        $locale = Locale::factory()->create(['code' => $this->faker->unique()->locale]);
        $tag = Tag::factory()->create(['name' => $this->faker->unique()->word()]);
        $translation = Translation::factory()->create([
            'key' => 'checkout.title',
            'content' => 'Paiement',
            'locale_id' => $locale->id,
        ]);
        $translation->tags()->attach($tag);

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->getJson('/api/translations?format=1')
            ->assertStatus(200)
            ->assertJsonStructure([$locale->code => [$tag->name => ['checkout' => ['title']]]]);
    }

    public function test_it_returns_data_format_when_requested()
    {
        $locale = Locale::factory()->create(['code' => $this->faker->unique()->locale]);
        $locale2 = Locale::factory()->create(['code' => $this->faker->unique()->locale]);

        Translation::factory()->create(['key' => 'home.title', 'content' => 'Home', 'locale_id' => $locale->id]);
        Translation::factory()->create(['key' => 'home.title', 'content' => 'Accueil', 'locale_id' => $locale2->id]);

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->getJson('/api/translations?format=1')
            ->assertStatus(200)
            ->assertJsonIsArray();
    }

    public function test_it_creates_a_translation_and_syncs_tags()
    {
        $locale = Locale::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        $payload = [
            'key' => 'checkout.subtitle',
            'content' => 'Secure Payment',
            'locale_id' => $locale->id,
            'tags' => $tags->pluck('id')->toArray(),
        ];

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson('/api/translations', $payload)
            ->assertStatus(200)
            ->assertJsonFragment(['key' => 'checkout.subtitle'])
            ->assertJsonFragment(['name' => $tags[0]->name]);
    }

    public function test_it_updates_a_translation()
    {
        $translation = Translation::factory()->create();
        $payload = ['content' => 'Updated Content'];

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->putJson("/api/translations/{$translation->id}", $payload)
            ->assertStatus(200)
            ->assertJsonFragment(['content' => 'Updated Content']);
    }

    public function test_it_deletes_a_translation_and_clears_cache()
    {
        $translation = Translation::factory()->create();
        $translation->tags()->attach(Tag::factory()->create());

        // First, assert the record exists in the database
        $this->assertDatabaseHas('translations', ['id' => $translation->id]);

        Cache::put('translations_test', 'dummy', 3600);
        $this->assertTrue(Cache::has('translations_test'));

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->deleteJson("/api/translations/{$translation->id}")
            ->assertStatus(200)
            ->assertJson(['message' => 'Translation deleted']);

        $this->assertFalse(Cache::has('translations_test'));
    }

    public function test_command_runs_successfully()
    {
        $tags = Tag::factory()->count(4)->create();

        // Assert that the console command runs without any exceptions and that the output is what we expect.
        $this->artisan('seed:translations', ['--total' => 10, '--chunk' => 2])
            ->assertSuccessful()
            ->expectsOutput('Seeding 10 translations in chunks of 2...')
            ->expectsOutput('Seeder completed.');

        // Verify that the correct number of translations were created.
        $this->assertCount(10, Translation::all());
    }

    public function test_unauthenticated_users_cannot_access_translations()
    {
        $this->getJson('/api/translations')->assertStatus(401);
        $this->postJson('/api/translations', [])->assertStatus(401);
        $this->putJson('/api/translations/1', [])->assertStatus(401);
        $this->deleteJson('/api/translations/1')->assertStatus(401);
    }
}
