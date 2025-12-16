<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SaturationDeduction;
use App\Models\StaffMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SaturationDeductionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = SaturationDeduction::with(['staffMember', 'deductionOption', 'author']);

        if ($request->has('staff_member_id')) {
            $query->where('staff_member_id', $request->staff_member_id);
        }

        if ($request->has('deduction_option_id')) {
            $query->where('deduction_option_id', $request->deduction_option_id);
        }

        $deductions = $request->has('per_page')
            ? $query->paginate($request->per_page)
            : $query->get();

        return response()->json([
            'data' => $deductions,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'staff_member_id' => ['required', 'exists:staff_members,id'],
            'deduction_option_id' => ['required', 'exists:deduction_options,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:fixed,percentage'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $validated['author_id'] = $request->user()->id;

        $deduction = SaturationDeduction::create($validated);

        return response()->json([
            'message' => 'Saturation deduction created successfully',
            'data' => $deduction->load(['staffMember', 'deductionOption']),
        ], 201);
    }

    public function show(SaturationDeduction $saturationDeduction): JsonResponse
    {
        return response()->json([
            'data' => $saturationDeduction->load(['staffMember', 'deductionOption', 'author']),
        ]);
    }

    public function update(Request $request, SaturationDeduction $saturationDeduction): JsonResponse
    {
        $validated = $request->validate([
            'deduction_option_id' => ['sometimes', 'exists:deduction_options,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'in:fixed,percentage'],
            'amount' => ['sometimes', 'numeric', 'min:0'],
        ]);

        $saturationDeduction->update($validated);

        return response()->json([
            'message' => 'Saturation deduction updated successfully',
            'data' => $saturationDeduction->fresh()->load(['staffMember', 'deductionOption']),
        ]);
    }

    public function destroy(SaturationDeduction $saturationDeduction): JsonResponse
    {
        $saturationDeduction->delete();

        return response()->json([
            'message' => 'Saturation deduction deleted successfully',
        ]);
    }

    public function byStaffMember(StaffMember $staffMember): JsonResponse
    {
        $deductions = $staffMember->saturationDeductions()->with('deductionOption')->get();

        return response()->json([
            'data' => $deductions,
        ]);
    }
}
