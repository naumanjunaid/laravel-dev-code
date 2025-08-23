<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TranslationRequest;
use App\Models\Translation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        $formatResponse = $request->query('format', '0');

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

        if ((int) $formatResponse === 1) {
            $translations = self::formatData($translations, $tags);
        }

        return response()->json($translations);
    }

    /**
     * Formatted out of the data
     */
    public function formatted(): JsonResponse
    {
        $translations = Translation::with(['locale', 'tags'])->get();
        $normalized = self::formatData($translations);

        return response()->json($normalized);
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

        return response()->json(['message' => 'Translation deleted']);
    }

    /**
     * Normalize translations into nested JSON by locale and tag.
     */
    private function formatData($translations, $allowedTags = null): array
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
}
