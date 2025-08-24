<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TranslationRequest;
use App\Models\Translation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TranslationController extends Controller
{
    /**
     * List translations (with optional filters).
     *
     * @group Translations
     *
     * @queryParam locale string Filter by locale code(s). Example: fr,en
     * @queryParam tag int Filter by tag ID(s). Example: 1,3
     * @queryParam key string Filter by translation key(s). Example: checkout.title
     * @queryParam content string Filter by content substring(s). Example: Paiement
     * @queryParam format boolean Return nested JSON format if `1`. Example: 1
     *
     * @response 200 scenario="success" [{
     *   "id": 1,
     *   "key": "checkout.title",
     *   "content": "Paiement",
     *   "locale": {
     *     "id": 2,
     *     "code": "fr",
     *     "name": "French"
     *   },
     *   "tags": [
     *     {"id": 1, "name": "mobile"},
     *     {"id": 2, "name": "desktop"}
     *   ]
     * }]
     * @response 401 {"message": "unauthenticated"}
     */
    public function index(Request $request)
    {
        // Filters
        $locales = $this->normalizeInput($request->locale);
        $tags = $this->normalizeInput($request->tag);
        $keys = $this->normalizeInput($request->key);
        $contents = $this->normalizeInput($request->content);

        $formatResponse = (int) $request->query('format', 0);

        $cacheKey = $this->generateCacheKey($locales, $tags, $keys, $contents, $formatResponse);

        $translations = Cache::remember($cacheKey, 3600, function () use ($locales, $tags, $keys, $contents, $formatResponse) {
            $query = Translation::query()->with([
                'locale',
                'tags' => function ($q) use ($tags) {
                    if ($tags) {
                        $q->whereIn('tags.id', $tags);
                    }
                },
            ]);

            // Locale filter
            if ($locales) {
                $query->whereHas('locale', fn ($q) => $q->whereIn('code', $locales));
            }

            // Tag filter (ensure translations have at least one tag)
            if ($tags) {
                $query->whereHas('tags', fn ($q) => $q->whereIn('tags.id', $tags));
            }

            // Key filter
            if ($keys) {
                $query->where(function ($q) use ($keys) {
                    foreach ($keys as $key) {
                        $q->orWhere('key', 'like', "%$key%");
                    }
                });
            }

            // Content filter
            if ($contents) {
                $query->where(function ($q) use ($contents) {
                    foreach ($contents as $content) {
                        $q->orWhere('content', 'like', "%$content%");
                    }
                });
            }

            $translations = $query->get();

            if ($formatResponse === 1) {
                $translations = self::formatData($translations, $tags);
            }

            return $translations;
        });

        return response()->json($translations);
    }

    /**
     * Create a new translation.
     *
     * @group Translations
     *
     * @bodyParam key string required The translation key. Example: checkout.title
     * @bodyParam content string required The translation content. Example: Paiement
     * @bodyParam locale_id int required The locale ID. Example: 2
     * @bodyParam tags array Optional list of tag IDs. Example: [1,2]
     *
     * @response 201 scenario="created" {
     *   "id": 3,
     *   "key": "checkout.title",
     *   "content": "Paiement",
     *   "locale_id": 2,
     *   "locale": {"id": 2, "code": "fr", "name": "French"},
     *   "tags": [{"id": 2, "name": "desktop"}, {"id": 3, "name": "web"}]
     * }
     * @response 401 {"message": "unauthenticated"}
     */
    public function store(TranslationRequest $request)
    {
        return $this->storeOrUpdate($request);
    }

    /**
     * Update an existing translation.
     *
     * @group Translations
     *
     * @urlParam id int required The ID of the translation. Example: 1
     *
     * @bodyParam key string The translation key. Example: checkout.title
     * @bodyParam content string The translation content. Example: Paiements
     * @bodyParam locale_id int The locale ID. Example: 2
     * @bodyParam tags array Optional list of tag IDs. Example: [1,2]
     *
     * @response 200 scenario="updated" {
     *   "id": 1,
     *   "key": "checkout.title",
     *   "content": "Paiements",
     *   "locale_id": 2,
     *   "locale": {"id": 2, "code": "fr", "name": "French"},
     *   "tags": [{"id": 1, "name": "mobile"}, {"id": 2, "name": "desktop"}]
     * }
     * @response 422 {
     *   "message": "The key has already been taken.",
     *   "errors": {"key": ["The key has already been taken."]}
     * }
     * @response 401 {"message": "unauthenticated"}
     */
    public function update(TranslationRequest $request, $id)
    {
        return $this->storeOrUpdate($request, $id);
    }

    /**
     * Internal helper to create or update a translation.
     */
    public function storeOrUpdate($request, $id = null): JsonResponse
    {
        $translation = Translation::updateOrCreate(
            ['id' => $id],
            $request->validated()
        );

        // Sync tags if provided
        if ($request->has('tags')) {
            $translation->tags()->sync($request->input('tags'));
        }

        $this->clearTranslationCache();

        return response()->json(
            Translation::with(['locale', 'tags'])->find($translation->id)
        );
    }

    /**
     * Delete a translation.
     *
     * @group Translations
     *
     * @urlParam id int required The ID of the translation to delete. Example: 1
     *
     * @response 200 {"message": "Translation deleted"}
     * @response 401 {"message": "unauthenticated"}
     */
    public function destroy(Translation $translation): JsonResponse
    {
        $translation->tags()->detach();
        $translation->delete();

        $this->clearTranslationCache();

        return response()->json(['message' => 'Translation deleted']);
    }

    /**
     * Normalize translations into nested JSON by locale and tag.
     *
     * @group Translations
     *
     * @queryParam locale string Filter by locale code(s). Example: fr,en
     * @queryParam tag int Filter by tag ID(s). Example: 1,3
     * @queryParam key string Filter by translation key(s). Example: checkout.title
     * @queryParam content string Filter by content substring(s). Example: Paiement
     * @queryParam format boolean Return nested JSON format if `1`. Example: 1
     *
     * @response 200 scenario="flat" [{
     *   "id": 1,
     *   "key": "checkout.title",
     *   "content": "Paiement",
     *   "locale": {
     *     "id": 2,
     *     "code": "fr",
     *     "name": "French"
     *   },
     *   "tags": [
     *     {"id": 1, "name": "mobile"},
     *     {"id": 2, "name": "desktop"}
     *   ]
     * }]
     * @response 200 scenario="nested" {
     *   "fr": {
     *     "mobile": {
     *       "checkout": {
     *         "title": "Paiement"
     *       }
     *     },
     *     "desktop": {
     *       "checkout": {
     *         "title": "Paiement"
     *       }
     *     }
     *   }
     * }
     * @response 401 {"message": "unauthenticated"}
     */
    private function formatData(Collection $translations, $allowedTags = null): array
    {
        $normalized = [];
        foreach ($translations as $translation) {
            $localeCode = $translation->locale->code;

            foreach ($translation->tags as $tag) {
                // Skip tags not in allowed list
                if ($allowedTags && ! in_array($tag->id, $allowedTags)) {
                    continue;
                }

                $segments = explode('.', $translation->key);
                $ref = &$normalized[$localeCode][$tag->name];

                // Build nested array for dot notation keys
                foreach ($segments as $segment) {
                    if (! isset($ref[$segment])) {
                        $ref[$segment] = [];
                    }
                    $ref = &$ref[$segment];
                }

                $ref = $translation->content; // assign value
                unset($ref); // break reference
            }
        }

        return $normalized;
    }

    /**
     * Helper to convert comma-separated or array input into array.
     */
    private function normalizeInput($input): ?array
    {
        if (! $input) {
            return null;
        }

        return is_array($input) ? $input : explode(',', $input);
    }

    /**
     * Generate cache key for a given query.
     */
    private function generateCacheKey($locales, $tags, $keys, $contents, $format): string
    {
        return 'translations_'.md5(
            implode(',', $locales ?? []).'|'.
                implode(',', $tags ?? []).'|'.
                implode(',', $keys ?? []).'|'.
                implode(',', $contents ?? []).'|'.
                $format
        );
    }

    /**
     * Clear all cached translation files (file or memory cache).
     */
    protected function clearTranslationCache(): void
    {
        // need a better way of cache busting but not botthered here
        // to delete everything as we are only dealing with simple API
        // otherwise need a mechanism to get the key of updated cache content
        // and delete only that item
        Cache::flush();
    }
}
