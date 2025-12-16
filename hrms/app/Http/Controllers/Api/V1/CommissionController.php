<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\StaffMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Commission::with(['staffMember', 'author']);

        if ($request->has('staff_member_id')) {
            $query->where('staff_member_id', $request->staff_member_id);
        }

        if ($request->has('active')) {
            $today = now()->toDateString();
            $query->where('start_date', '<=', $today)
                ->where('end_date', '>=', $today);
        }

        $commissions = $request->has('per_page')
            ? $query->paginate($request->per_page)
            : $query->get();

        return response()->json([
            'data' => $commissions,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'staff_member_id' => ['required', 'exists:staff_members,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:fixed,percentage'],
            'amount' => ['required', 'numeric', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $validated['author_id'] = $request->user()->id;

        $commission = Commission::create($validated);

        return response()->json([
            'message' => 'Commission created successfully',
            'data' => $commission->load('staffMember'),
        ], 201);
    }

    public function show(Commission $commission): JsonResponse
    {
        return response()->json([
            'data' => $commission->load(['staffMember', 'author']),
        ]);
    }

    public function update(Request $request, Commission $commission): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'in:fixed,percentage'],
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date'],
        ]);

        $commission->update($validated);

        return response()->json([
            'message' => 'Commission updated successfully',
            'data' => $commission->fresh()->load('staffMember'),
        ]);
    }

    public function destroy(Commission $commission): JsonResponse
    {
        $commission->delete();

        return response()->json([
            'message' => 'Commission deleted successfully',
        ]);
    }

    public function byStaffMember(StaffMember $staffMember): JsonResponse
    {
        $commissions = $staffMember->commissions()->get();

        return response()->json([
            'data' => $commissions,
        ]);
    }
}
