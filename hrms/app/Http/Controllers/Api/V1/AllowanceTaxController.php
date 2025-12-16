<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AllowanceTax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AllowanceTaxController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AllowanceTax::with(['allowance', 'author']);

        if ($request->has('allowance_id')) {
            $query->where('allowance_id', $request->allowance_id);
        }

        $allowanceTaxes = $request->has('per_page')
            ? $query->paginate($request->per_page)
            : $query->get();

        return response()->json([
            'data' => $allowanceTaxes,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'allowance_id' => ['required', 'exists:allowances,id', 'unique:allowance_taxes,allowance_id'],
            'tax_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $validated['author_id'] = $request->user()->id;

        $allowanceTax = AllowanceTax::create($validated);

        return response()->json([
            'message' => 'Allowance tax created successfully',
            'data' => $allowanceTax->load('allowance'),
        ], 201);
    }

    public function show(AllowanceTax $allowanceTax): JsonResponse
    {
        return response()->json([
            'data' => $allowanceTax->load(['allowance', 'author']),
        ]);
    }

    public function update(Request $request, AllowanceTax $allowanceTax): JsonResponse
    {
        $validated = $request->validate([
            'tax_percentage' => ['sometimes', 'numeric', 'min:0', 'max:100'],
        ]);

        $allowanceTax->update($validated);

        return response()->json([
            'message' => 'Allowance tax updated successfully',
            'data' => $allowanceTax->fresh()->load('allowance'),
        ]);
    }

    public function destroy(AllowanceTax $allowanceTax): JsonResponse
    {
        $allowanceTax->delete();

        return response()->json([
            'message' => 'Allowance tax deleted successfully',
        ]);
    }
}
