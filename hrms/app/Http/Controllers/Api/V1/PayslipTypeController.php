<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PayslipType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayslipTypeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PayslipType::with('author');

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $payslipTypes = $request->has('per_page')
            ? $query->paginate($request->per_page)
            : $query->get();

        return response()->json([
            'data' => $payslipTypes,
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

        $payslipType = PayslipType::create($validated);

        return response()->json([
            'message' => 'Payslip type created successfully',
            'data' => $payslipType,
        ], 201);
    }

    public function show(PayslipType $payslipType): JsonResponse
    {
        return response()->json([
            'data' => $payslipType->load('author'),
        ]);
    }

    public function update(Request $request, PayslipType $payslipType): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $payslipType->update($validated);

        return response()->json([
            'message' => 'Payslip type updated successfully',
            'data' => $payslipType->fresh(),
        ]);
    }

    public function destroy(PayslipType $payslipType): JsonResponse
    {
        $payslipType->delete();

        return response()->json([
            'message' => 'Payslip type deleted successfully',
        ]);
    }
}
