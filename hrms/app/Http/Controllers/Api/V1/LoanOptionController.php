<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LoanOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoanOptionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = LoanOption::with('author');

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $loanOptions = $request->has('per_page')
            ? $query->paginate($request->per_page)
            : $query->get();

        return response()->json([
            'data' => $loanOptions,
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

        $loanOption = LoanOption::create($validated);

        return response()->json([
            'message' => 'Loan option created successfully',
            'data' => $loanOption,
        ], 201);
    }

    public function show(LoanOption $loanOption): JsonResponse
    {
        return response()->json([
            'data' => $loanOption->load('author'),
        ]);
    }

    public function update(Request $request, LoanOption $loanOption): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $loanOption->update($validated);

        return response()->json([
            'message' => 'Loan option updated successfully',
            'data' => $loanOption->fresh(),
        ]);
    }

    public function destroy(LoanOption $loanOption): JsonResponse
    {
        $loanOption->delete();

        return response()->json([
            'message' => 'Loan option deleted successfully',
        ]);
    }
}
