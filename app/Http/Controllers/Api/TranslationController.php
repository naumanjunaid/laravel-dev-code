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
     * List translations with optional filters and nested export.
     */
    public function index(Request $request)
    {
        // Filters
        $locales  = $this->normalizeInput($request->locale);
        $tags     = $this->normalizeInput($request->tag);
        $keys     = $this->normalizeInput($request->key);
        $contents = $this->normalizeInput($request->content);

        $formatResponse = (int) $request->query('format', '0');

        $cacheKey = $this->generateCacheKey($locales, $tags, $keys, $contents, $formatResponse);
        $translations = Cache::remember(
            $cacheKey,
            3600,
            function () use ($locales, $tags, $keys, $contents, $formatResponse) {
                $query = Translation::query()->with([
                    'locale',
                    'tags' => function ($q) use ($tags) {
                        if ($tags) {
                            $q->whereIn('tags.id', $tags);
                        }
                    }
                ]);

                // Locale filter
                if ($locales) {
                    $query->whereHas('locale', fn($q) => $q->whereIn('code', $locales));
                }

                // Tag filter (ensure translations have at least one tag)
                if ($tags) {
                    $query->whereHas('tags', fn($q) => $q->whereIn('tags.id', $tags));
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
            }
        );

        return response()->json($translations);
    }

    /**
     * Store or update a translation.
     */
    public function storeOrUpdate(TranslationRequest $request, ?Translation $translation = null): JsonResponse
    {
        $translation = $translation ?? new Translation();
        $translation->fill($request->validated());
        $translation->save();

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
     */
    private function formatData(Collection $translations, $allowedTags = null): array
    {
        $normalized = [];
        foreach ($translations as $translation) {
            $localeCode = $translation->locale->code;

            foreach ($translation->tags as $tag) {
                // Skip tags not in allowed list
                if ($allowedTags && !in_array($tag->id, $allowedTags)) {
                    continue;
                }

                $segments = explode('.', $translation->key);
                $ref = &$normalized[$localeCode][$tag->name];

                // Build nested array for dot notation keys
                foreach ($segments as $segment) {
                    if (!isset($ref[$segment])) {
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
        if (!$input) return null;
        return is_array($input) ? $input : explode(',', $input);
    }

    /**
     * Generate cache key for a given query.
     */
    private function generateCacheKey($locales, $tags, $keys, $contents, $format): string
    {
        return 'translations_' . md5(
            implode(',', $locales ?? []) . '|' .
                implode(',', $tags ?? []) . '|' .
                implode(',', $keys ?? []) . '|' .
                implode(',', $contents ?? []) . '|' .
                $format
        );
    }

    /**
     * Clear all cached translation files (file or memory cache).
     */
    protected function clearTranslationCache(): void
    {
        Cache::flush();
    }
}
