<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AllowanceOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AllowanceOptionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AllowanceOption::with('author');

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $allowanceOptions = $request->has('per_page')
            ? $query->paginate($request->per_page)
            : $query->get();

        return response()->json([
            'data' => $allowanceOptions,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $validated['author_id'] = $request->user()->id;

        $allowanceOption = AllowanceOption::create($validated);

        return response()->json([
            'message' => 'Allowance option created successfully',
            'data' => $allowanceOption,
        ], 201);
    }

    public function show(AllowanceOption $allowanceOption): JsonResponse
    {
        return response()->json([
            'data' => $allowanceOption->load('author'),
        ]);
    }

    public function update(Request $request, AllowanceOption $allowanceOption): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $allowanceOption->update($validated);

        return response()->json([
            'message' => 'Allowance option updated successfully',
            'data' => $allowanceOption->fresh(),
        ]);
    }

    public function destroy(AllowanceOption $allowanceOption): JsonResponse
    {
        $allowanceOption->delete();

        return response()->json([
            'message' => 'Allowance option deleted successfully',
        ]);
    }
}
