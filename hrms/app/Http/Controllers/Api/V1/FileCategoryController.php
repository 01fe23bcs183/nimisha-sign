<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FileCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FileCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = FileCategory::with('author');

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('is_mandatory')) {
            $query->where('is_mandatory', $request->boolean('is_mandatory'));
        }

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $fileCategories = $request->has('per_page')
            ? $query->paginate($request->per_page)
            : $query->get();

        return response()->json([
            'data' => $fileCategories,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_mandatory' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $validated['author_id'] = $request->user()->id;

        $fileCategory = FileCategory::create($validated);

        return response()->json([
            'message' => 'File category created successfully',
            'data' => $fileCategory,
        ], 201);
    }

    public function show(FileCategory $fileCategory): JsonResponse
    {
        return response()->json([
            'data' => $fileCategory->load('author'),
        ]);
    }

    public function update(Request $request, FileCategory $fileCategory): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_mandatory' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $fileCategory->update($validated);

        return response()->json([
            'message' => 'File category updated successfully',
            'data' => $fileCategory->fresh(),
        ]);
    }

    public function destroy(FileCategory $fileCategory): JsonResponse
    {
        $fileCategory->delete();

        return response()->json([
            'message' => 'File category deleted successfully',
        ]);
    }
}
