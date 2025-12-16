<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ExitCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExitCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ExitCategory::with('author');

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

        $category = ExitCategory::create($validated);

        return response()->json([
            'message' => 'Exit category created successfully',
            'data' => $category,
        ], 201);
    }

    public function show(ExitCategory $exitCategory): JsonResponse
    {
        return response()->json([
            'data' => $exitCategory->load('author'),
        ]);
    }

    public function update(Request $request, ExitCategory $exitCategory): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $exitCategory->update($validated);

        return response()->json([
            'message' => 'Exit category updated successfully',
            'data' => $exitCategory->fresh(),
        ]);
    }

    public function destroy(ExitCategory $exitCategory): JsonResponse
    {
        $exitCategory->delete();

        return response()->json([
            'message' => 'Exit category deleted successfully',
        ]);
    }
}
