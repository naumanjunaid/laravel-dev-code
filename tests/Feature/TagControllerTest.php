<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\Translation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_all_tags()
    {
        Tag::factory()->count(3)->create();

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->getJson('/api/tags')
            ->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_it_creates_a_tag()
    {
        $payload = ['name' => 'Tablet'];

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson('/api/tags', $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['name' => 'Tablet']);
    }

    public function test_it_updates_a_tag()
    {
        $tag = Tag::factory()->create();

        $payload = ['name' => 'UpdatedTag'];

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->putJson("/api/tags/{$tag->id}", $payload)
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'UpdatedTag']);
    }

    public function test_it_deletes_a_tag()
    {
        $tag = Tag::factory()->create();

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->deleteJson("/api/tags/{$tag->id}")
            ->assertStatus(200)
            ->assertJson(['message' => 'Tag deleted']);

        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    public function test_tag_morphed_by_many_translations()
    {
        // Create a tag
        $tag = Tag::factory()->create();

        // Create some translations
        $translations = Translation::factory()->count(2)->create();

        // Attach the translations to the tag using the relationship
        $tag->translations()->attach($translations->pluck('id'));

        // Assert that the relationship returns the correct number of translations
        $this->assertCount(2, $tag->translations);

        // Assert that the returned collection contains the created translations
        $this->assertEqualsCanonicalizing($translations->pluck('id')->toArray(), $tag->translations->pluck('id')->toArray());
    }

    public function test_unauthenticated_users_cannot_access_tags()
    {
        $this->getJson('/api/tags')->assertStatus(401);
        $this->postJson('/api/tags', ['name' => 'Test'])->assertStatus(401);
        $this->putJson('/api/tags/1', ['name' => 'Test'])->assertStatus(401);
        $this->deleteJson('/api/tags/1')->assertStatus(401);
    }
}
