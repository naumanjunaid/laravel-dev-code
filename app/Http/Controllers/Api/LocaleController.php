<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LocaleRequest;
use App\Models\Locale;

class LocaleController extends Controller
{
    public function index()
    {
        return response()->json(Locale::all());
    }

    public function storeOrUpdate(LocaleRequest $request, $id = null)
    {
        $locale = Locale::updateOrCreate(
            ['id' => $id],
            $request->validated()
        );

        return response()->json($locale, $id ? 200 : 201);
    }

    public function destroy($id)
    {
        Locale::findOrFail($id)->delete();

        return response()->json(['message' => 'Locale deleted']);
    }
}
