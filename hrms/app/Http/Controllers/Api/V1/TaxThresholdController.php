<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TaxThreshold;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaxThresholdController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = TaxThreshold::with('author');

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $taxThresholds = $request->has('per_page')
            ? $query->paginate($request->per_page)
            : $query->get();

        return response()->json([
            'data' => $taxThresholds,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'threshold_amount' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['author_id'] = $request->user()->id;

        $taxThreshold = TaxThreshold::create($validated);

        return response()->json([
            'message' => 'Tax threshold created successfully',
            'data' => $taxThreshold,
        ], 201);
    }

    public function show(TaxThreshold $taxThreshold): JsonResponse
    {
        return response()->json([
            'data' => $taxThreshold->load('author'),
        ]);
    }

    public function update(Request $request, TaxThreshold $taxThreshold): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'threshold_amount' => ['sometimes', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        $taxThreshold->update($validated);

        return response()->json([
            'message' => 'Tax threshold updated successfully',
            'data' => $taxThreshold->fresh(),
        ]);
    }

    public function destroy(TaxThreshold $taxThreshold): JsonResponse
    {
        $taxThreshold->delete();

        return response()->json([
            'message' => 'Tax threshold deleted successfully',
        ]);
    }
}
