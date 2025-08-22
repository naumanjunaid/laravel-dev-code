<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TagRequest;
use App\Models\Tag;

class TagController extends Controller
{
    public function index()
    {
        return response()->json(Tag::all());
    }

    public function storeOrUpdate(TagRequest $request, $id = null)
    {
        $tag = Tag::updateOrCreate(
            ['id' => $id],
            $request->validated()
        );

        return response()->json($tag, $id ? 200 : 201);
    }

    public function destroy($id)
    {
        Tag::findOrFail($id)->delete();

        return response()->json(['message' => 'Tag deleted']);
    }
}
