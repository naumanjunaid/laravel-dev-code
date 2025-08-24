<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TagRequest;
use App\Models\Tag;

class TagController extends Controller
{
    /**
     * Get all tags
     *
     * @group Tags
     *
     * @response 200 scenario="success" [
     *   {
     *     "id": 1,
     *     "name": "mobile",
     *     "created_at": "2025-08-22 23:45:31",
     *     "updated_at": "2025-08-22 23:45:31"
     *   },
     *   {
     *     "id": 2,
     *     "name": "desktop",
     *     "created_at": "2025-08-22 23:45:31",
     *     "updated_at": "2025-08-22 23:45:31"
     *   },
     *   {
     *     "id": 3,
     *     "name": "web",
     *     "created_at": "2025-08-22 23:45:31",
     *     "updated_at": "2025-08-22 23:45:31"
     *   }
     * ]
     * @response 401 {"message": "unauthenticated"}
     */
    public function index()
    {
        return response()->json(Tag::all());
    }

    /**
     * Create a new tag
     *
     * @group Tags
     *
     * @bodyParam name string required The tag name. Example: Tablet
     *
     * @response 201 scenario="created" {
     *   "id": 7,
     *   "name": "Tablet",
     *   "created_at": "2025-08-24 11:57:58",
     *   "updated_at": "2025-08-24 11:57:58"
     * }
     * @response 401 {"message": "unauthenticated"}
     */
    public function store(TagRequest $request)
    {
        return $this->storeOrUpdate($request);
    }

    /**
     * Update an existing tag
     *
     * @group Tags
     *
     * @urlParam id int required The ID of the tag. Example: 7
     *
     * @bodyParam name string required The tag name. Example: Tablet
     *
     * @response 200 scenario="updated" {
     *   "id": 7,
     *   "name": "Tablet",
     *   "created_at": "2025-08-24 11:57:58",
     *   "updated_at": "2025-08-24 12:09:43"
     * }
     * @response 422 {
     *   "message": "The name has already been taken.",
     *   "errors": {
     *     "name": ["The name has already been taken."]
     *   }
     * }
     * @response 401 {"message": "unauthenticated"}
     */
    public function update(TagRequest $request, $id)
    {
        return $this->storeOrUpdate($request, $id);
    }

    /**
     * Internal helper to create or update a tag.
     */
    protected function storeOrUpdate($request, $id = null)
    {
        $tag = Tag::updateOrCreate(
            ['id' => $id],
            $request->validated()
        );

        return response()->json($tag, $id ? 200 : 201);
    }

    /**
     * Delete a tag
     *
     * @group Tags
     *
     * @urlParam id int required The ID of the tag to delete. Example: 1
     *
     * @response 200 {"message": "Tag deleted"}
     * @response 401 {"message": "unauthenticated"}
     */
    public function destroy($id)
    {
        Tag::findOrFail($id)->delete();

        return response()->json(['message' => 'Tag deleted']);
    }
}
