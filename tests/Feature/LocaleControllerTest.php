<?php

namespace Tests\Feature;

use App\Models\Locale;
use App\Models\Translation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LocaleControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function authenticate()
    {
        $user = User::factory()->create();

        return $this->actingAs($user, 'sanctum');
    }

    public function test_it_lists_locales()
    {
        $this->authenticate();
        Locale::factory()->count(3)->create();

        $response = $this->getJson('/api/locales');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_it_creates_a_locale()
    {
        $this->authenticate();

        $data = [
            'code' => 'ar',
            'name' => 'Arabic',
        ];

        $response = $this->postJson('/api/locales', $data);

        $response->assertStatus(201)
            ->assertJsonFragment($data);

        $this->assertDatabaseHas('locales', $data);
    }

    public function test_it_updates_a_locale()
    {
        $this->authenticate();
        $locale = Locale::factory()->create([
            'code' => 'en',
            'name' => 'English',
        ]);

        $updateData = [
            'code' => 'fr',
            'name' => 'French',
        ];

        $response = $this->putJson("/api/locales/{$locale->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment($updateData);

        $this->assertDatabaseHas('locales', $updateData);
    }

    public function test_it_deletes_a_locale()
    {
        $this->authenticate();
        $locale = Locale::factory()->create();

        $response = $this->deleteJson("/api/locales/{$locale->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Locale deleted',
            ]);

        $this->assertDatabaseMissing('locales', ['id' => $locale->id]);
    }

    public function test_locale_has_many_translations()
    {
        // Create a locale
        $locale = Locale::factory()->create(['code' => $this->faker->unique()->locale]);

        // Create some translations associated with that locale
        $translations = Translation::factory()->count(3)->create(['locale_id' => $locale->id]);

        // Assert that the locale's translations relationship returns the correct count
        $this->assertCount(3, $locale->translations);

        // Assert that the returned collection contains the created translations
        $this->assertEqualsCanonicalizing($translations->pluck('id')->toArray(), $locale->translations->pluck('id')->toArray());
    }

    public function test_unauthenticated_users_cannot_access_locales()
    {
        $response = $this->getJson('/api/locales');
        $response->assertStatus(401);
    }
}
