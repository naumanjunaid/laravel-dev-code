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
    public function index(Request $request)
    {
        $hasLocale = $request->has('locale');
        $hasTag = $request->has('tag');
        $hasKey = $request->has('key');
        $hasContent = $request->has('content');
        $formatResponse = $request->query('format', '0');

        $query = Translation::with(['locale', 'tags']);

        if ($hasLocale || $hasTag || $hasKey || $hasContent) {
            // Filter by locale (code)
            if ($hasLocale) {
                $query->whereHas('locale', function ($q) use ($request) {
                    $q->where('code', $request->locale);
                });
            }

            // Filter by tags (can be single id or array of ids)
            if ($hasTag) {
                $tags = is_array($request->tag) ? $request->tag : [$request->tag];
                $query->whereHas('tags', function ($q) use ($tags) {
                    $q->whereIn('tags.id', $tags);
                });
            }

            // Filter by key (partial search)
            if ($hasKey) {
                $query->where('key', 'like', '%' . $request->key . '%');
            }

            if ($hasContent) {
                $query->where('value', 'like', '%' . $request->content . '%');
            }
        }

        $translations = $query->get();

        if ((int) $formatResponse === 1) {
            $translations = self::formatData($translations);
        }

        return response()->json($translations);
    }

    public function formatted(): JsonResponse
    {
        $translations = Translation::with(['locale', 'tags'])->get();
        $normalized = self::formatData($translations);

        return response()->json($normalized);
    }

    private function formatData(Collection $translations): array
    {
        $normalized = [];
        foreach ($translations as $t) {
            $localeCode = $t->locale->code;

            if (!isset($normalized[$localeCode])) {
                $normalized[$localeCode] = [];
            }

            // if no tags, put in a default 'all' group
            $tags = $t->tags->pluck('name')->all() ?: ['all'];

            foreach ($tags as $tag) {
                if (!isset($normalized[$localeCode][$tag])) {
                    $normalized[$localeCode][$tag] = [];
                }

                // Build nested structure from dot notation
                $parts = explode('.', $t->key);
                $ref = &$normalized[$localeCode][$tag];

                foreach ($parts as $i => $part) {
                    if ($i === count($parts) - 1) {
                        $ref[$part] = $t->content;
                    } else {
                        if (!isset($ref[$part])) {
                            $ref[$part] = [];
                        }
                        $ref = &$ref[$part];
                    }
                }

                unset($ref); // break reference
            }
        }

        return $normalized;
    }

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

    public function destroy(Translation $translation): JsonResponse
    {
        $translation->tags()->detach();
        $translation->delete();

        return response()->json(['message' => 'Translation deleted']);
    }
}
