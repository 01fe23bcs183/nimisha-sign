<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\RecognitionCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecognitionCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = RecognitionCategory::with('author');

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $categories = $request->has('per_page')
            ? $query->paginate($request->per_page)
            : $query->get();

        return response()->json([
            'data' => $categories,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $validated['author_id'] = $request->user()->id;

        $category = RecognitionCategory::create($validated);

        return response()->json([
            'message' => 'Recognition category created successfully',
            'data' => $category,
        ], 201);
    }

    public function show(RecognitionCategory $recognitionCategory): JsonResponse
    {
        return response()->json([
            'data' => $recognitionCategory->load('author'),
        ]);
    }

    public function update(Request $request, RecognitionCategory $recognitionCategory): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $recognitionCategory->update($validated);

        return response()->json([
            'message' => 'Recognition category updated successfully',
            'data' => $recognitionCategory->fresh(),
        ]);
    }

    public function destroy(RecognitionCategory $recognitionCategory): JsonResponse
    {
        $recognitionCategory->delete();

        return response()->json([
            'message' => 'Recognition category deleted successfully',
        ]);
    }
}
