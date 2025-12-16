<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DeductionOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeductionOptionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = DeductionOption::with('author');

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $deductionOptions = $request->has('per_page')
            ? $query->paginate($request->per_page)
            : $query->get();

        return response()->json([
            'data' => $deductionOptions,
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

        $deductionOption = DeductionOption::create($validated);

        return response()->json([
            'message' => 'Deduction option created successfully',
            'data' => $deductionOption,
        ], 201);
    }

    public function show(DeductionOption $deductionOption): JsonResponse
    {
        return response()->json([
            'data' => $deductionOption->load('author'),
        ]);
    }

    public function update(Request $request, DeductionOption $deductionOption): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $deductionOption->update($validated);

        return response()->json([
            'message' => 'Deduction option updated successfully',
            'data' => $deductionOption->fresh(),
        ]);
    }

    public function destroy(DeductionOption $deductionOption): JsonResponse
    {
        $deductionOption->delete();

        return response()->json([
            'message' => 'Deduction option deleted successfully',
        ]);
    }
}
