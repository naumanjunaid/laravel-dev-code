<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LocaleRequest;
use App\Models\Locale;

class LocaleController extends Controller
{
    /**
     * Get all locales
     *
     * @group Locales
     *
     * @response 200 scenario="success" [
     *   {
     *     "id": 1,
     *     "code": "en",
     *     "name": "English",
     *     "created_at": "2025-08-22 22:30:23",
     *     "updated_at": "2025-08-22 22:30:23"
     *   },
     *   {
     *     "id": 2,
     *     "code": "fr",
     *     "name": "French",
     *     "created_at": "2025-08-22 22:30:23",
     *     "updated_at": "2025-08-22 22:30:23"
     *   },
     *   {
     *     "id": 3,
     *     "code": "es",
     *     "name": "Spanish",
     *     "created_at": "2025-08-22 22:30:23",
     *     "updated_at": "2025-08-22 22:30:23"
     *   }
     * ]
     * @response 401 {"message": "unauthenticated"}
     */
    public function index()
    {
        return response()->json(Locale::all());
    }

    /**
     * Create a new locale
     *
     * @group Locales
     *
     * @bodyParam code string required The locale code. Example: en
     * @bodyParam name string required The locale name. Example: English
     *
     * @response 201 scenario="created" {
     *   "id": 7,
     *   "code": "ar",
     *   "name": "Arabic",
     *   "created_at": "2025-08-24 11:57:58",
     *   "updated_at": "2025-08-24 11:57:58"
     * }
     * @response 401 {"message": "unauthenticated"}
     */
    public function store(LocaleRequest $request)
    {
        return $this->storeOrUpdate($request);
    }

    /**
     * Update an existing locale
     *
     * @group Locales
     *
     * @urlParam id int required The ID of the locale. Example: 1
     *
     * @bodyParam code string required The locale code. Example: en
     * @bodyParam name string required The locale name. Example: English
     *
     * @response 200 scenario="updated" {
     *   "id": 7,
     *   "code": "ar",
     *   "name": "Arabic",
     *   "created_at": "2025-08-24 11:57:58",
     *   "updated_at": "2025-08-24 12:09:43"
     * }
     * @response 422 {
     *   "message": "The code has already been taken.",
     *   "errors": {
     *     "code": ["The code has already been taken."]
     *   }
     * }
     * @response 401 {"message": "unauthenticated"}
     */
    public function update(LocaleRequest $request, $id)
    {
        return $this->storeOrUpdate($request, $id);
    }

    /**
     * Internal helper to create or update a locale.
     */
    protected function storeOrUpdate(LocaleRequest $request, $id = null)
    {
        $locale = Locale::updateOrCreate(
            ['id' => $id],
            $request->validated()
        );

        return response()->json($locale, $id ? 200 : 201);
    }

    /**
     * Delete a locale
     *
     * @group Locales
     *
     * @urlParam id int required The ID of the locale to delete. Example: 1
     *
     * @response 200 {"message": "Locale deleted"}
     * @response 401 {"message": "unauthenticated"}
     */
    public function destroy($id)
    {
        Locale::findOrFail($id)->delete();

        return response()->json(['message' => 'Locale deleted']);
    }
}
