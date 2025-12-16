<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Allowance;
use App\Models\StaffMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AllowanceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Allowance::with(['staffMember', 'allowanceOption', 'author']);

        if ($request->has('staff_member_id')) {
            $query->where('staff_member_id', $request->staff_member_id);
        }

        if ($request->has('allowance_option_id')) {
            $query->where('allowance_option_id', $request->allowance_option_id);
        }

        $allowances = $request->has('per_page')
            ? $query->paginate($request->per_page)
            : $query->get();

        return response()->json([
            'data' => $allowances,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'staff_member_id' => ['required', 'exists:staff_members,id'],
            'allowance_option_id' => ['required', 'exists:allowance_options,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:fixed,percentage'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $validated['author_id'] = $request->user()->id;

        $allowance = Allowance::create($validated);

        return response()->json([
            'message' => 'Allowance created successfully',
            'data' => $allowance->load(['staffMember', 'allowanceOption']),
        ], 201);
    }

    public function show(Allowance $allowance): JsonResponse
    {
        return response()->json([
            'data' => $allowance->load(['staffMember', 'allowanceOption', 'author']),
        ]);
    }

    public function update(Request $request, Allowance $allowance): JsonResponse
    {
        $validated = $request->validate([
            'allowance_option_id' => ['sometimes', 'exists:allowance_options,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'in:fixed,percentage'],
            'amount' => ['sometimes', 'numeric', 'min:0'],
        ]);

        $allowance->update($validated);

        return response()->json([
            'message' => 'Allowance updated successfully',
            'data' => $allowance->fresh()->load(['staffMember', 'allowanceOption']),
        ]);
    }

    public function destroy(Allowance $allowance): JsonResponse
    {
        $allowance->delete();

        return response()->json([
            'message' => 'Allowance deleted successfully',
        ]);
    }

    public function byStaffMember(StaffMember $staffMember): JsonResponse
    {
        $allowances = $staffMember->allowances()->with('allowanceOption')->get();

        return response()->json([
            'data' => $allowances,
        ]);
    }
}
