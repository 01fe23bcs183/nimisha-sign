<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TaxBracket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaxBracketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = TaxBracket::with('author');

        $taxBrackets = $request->has('per_page')
            ? $query->orderBy('from_amount', 'asc')->paginate($request->per_page)
            : $query->orderBy('from_amount', 'asc')->get();

        return response()->json([
            'data' => $taxBrackets,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'from_amount' => ['required', 'numeric', 'min:0'],
            'to_amount' => ['required', 'numeric', 'gt:from_amount'],
            'fixed_amount' => ['required', 'numeric', 'min:0'],
            'percentage' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $validated['author_id'] = $request->user()->id;

        $taxBracket = TaxBracket::create($validated);

        return response()->json([
            'message' => 'Tax bracket created successfully',
            'data' => $taxBracket,
        ], 201);
    }

    public function show(TaxBracket $taxBracket): JsonResponse
    {
        return response()->json([
            'data' => $taxBracket->load('author'),
        ]);
    }

    public function update(Request $request, TaxBracket $taxBracket): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'from_amount' => ['sometimes', 'numeric', 'min:0'],
            'to_amount' => ['sometimes', 'numeric'],
            'fixed_amount' => ['sometimes', 'numeric', 'min:0'],
            'percentage' => ['sometimes', 'numeric', 'min:0', 'max:100'],
        ]);

        $taxBracket->update($validated);

        return response()->json([
            'message' => 'Tax bracket updated successfully',
            'data' => $taxBracket->fresh(),
        ]);
    }

    public function destroy(TaxBracket $taxBracket): JsonResponse
    {
        $taxBracket->delete();

        return response()->json([
            'message' => 'Tax bracket deleted successfully',
        ]);
    }

    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $tax = TaxBracket::calculateTax($validated['amount']);

        return response()->json([
            'data' => [
                'amount' => $validated['amount'],
                'tax' => $tax,
            ],
        ]);
    }
}
