<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TaxRebate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaxRebateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = TaxRebate::with('author');

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $taxRebates = $request->has('per_page')
            ? $query->paginate($request->per_page)
            : $query->get();

        return response()->json([
            'data' => $taxRebates,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['author_id'] = $request->user()->id;

        $taxRebate = TaxRebate::create($validated);

        return response()->json([
            'message' => 'Tax rebate created successfully',
            'data' => $taxRebate,
        ], 201);
    }

    public function show(TaxRebate $taxRebate): JsonResponse
    {
        return response()->json([
            'data' => $taxRebate->load('author'),
        ]);
    }

    public function update(Request $request, TaxRebate $taxRebate): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        $taxRebate->update($validated);

        return response()->json([
            'message' => 'Tax rebate updated successfully',
            'data' => $taxRebate->fresh(),
        ]);
    }

    public function destroy(TaxRebate $taxRebate): JsonResponse
    {
        $taxRebate->delete();

        return response()->json([
            'message' => 'Tax rebate deleted successfully',
        ]);
    }
}
