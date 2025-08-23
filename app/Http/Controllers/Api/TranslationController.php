<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TranslationRequest;
use App\Models\Translation;
use Illuminate\Http\JsonResponse;

class TranslationController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Translation::with(['locale', 'tags'])->get());
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
