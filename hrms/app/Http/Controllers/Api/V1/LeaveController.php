<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\StaffMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Leave::with(['staffMember', 'leaveType', 'approvedByUser', 'author']);

        if ($request->has('staff_member_id')) {
            $query->where('staff_member_id', $request->staff_member_id);
        }

        if ($request->has('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('end_date', '<=', $request->date_to);
        }

        if ($request->has('month')) {
            $query->whereMonth('start_date', $request->month);
        }

        if ($request->has('year')) {
            $query->whereYear('start_date', $request->year);
        }

        $leaves = $request->has('per_page')
            ? $query->orderBy('applied_on', 'desc')->paginate($request->per_page)
            : $query->orderBy('applied_on', 'desc')->get();

        return response()->json([
            'data' => $leaves,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'staff_member_id' => ['required', 'exists:staff_members,id'],
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'leave_reason' => ['nullable', 'string'],
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['applied_on'] = now()->toDateString();
        $validated['status'] = 'pending';
        $validated['author_id'] = $request->user()->id;

        $leave = Leave::create($validated);

        return response()->json([
            'message' => 'Leave application submitted successfully',
            'data' => $leave->load(['staffMember', 'leaveType']),
        ], 201);
    }

    public function show(Leave $leave): JsonResponse
    {
        return response()->json([
            'data' => $leave->load(['staffMember', 'leaveType', 'approvedByUser', 'author']),
        ]);
    }

    public function update(Request $request, Leave $leave): JsonResponse
    {
        if ($leave->status !== 'pending') {
            return response()->json([
                'message' => 'Cannot update a leave that has been processed',
            ], 422);
        }

        $validated = $request->validate([
            'leave_type_id' => ['sometimes', 'exists:leave_types,id'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date'],
            'leave_reason' => ['nullable', 'string'],
        ]);

        $leave->update($validated);

        return response()->json([
            'message' => 'Leave updated successfully',
            'data' => $leave->fresh()->load(['staffMember', 'leaveType']),
        ]);
    }

    public function approve(Request $request, Leave $leave): JsonResponse
    {
        $validated = $request->validate([
            'remark' => ['nullable', 'string'],
        ]);

        $leave->update([
            'status' => 'approved',
            'remark' => $validated['remark'] ?? null,
            'approved_by' => $request->user()->id,
            'approved_date' => now()->toDateString(),
        ]);

        return response()->json([
            'message' => 'Leave approved successfully',
            'data' => $leave->fresh()->load(['staffMember', 'leaveType', 'approvedByUser']),
        ]);
    }

    public function reject(Request $request, Leave $leave): JsonResponse
    {
        $validated = $request->validate([
            'remark' => ['nullable', 'string'],
        ]);

        $leave->update([
            'status' => 'rejected',
            'remark' => $validated['remark'] ?? null,
            'approved_by' => $request->user()->id,
            'approved_date' => now()->toDateString(),
        ]);

        return response()->json([
            'message' => 'Leave rejected',
            'data' => $leave->fresh()->load(['staffMember', 'leaveType']),
        ]);
    }

    public function destroy(Leave $leave): JsonResponse
    {
        if ($leave->status !== 'pending') {
            return response()->json([
                'message' => 'Cannot delete a leave that has been processed',
            ], 422);
        }

        $leave->delete();

        return response()->json([
            'message' => 'Leave deleted successfully',
        ]);
    }

    public function balance(Request $request, StaffMember $staffMember): JsonResponse
    {
        $year = $request->get('year', now()->year);

        $leaveTypes = LeaveType::where('is_active', true)->get();
        $balance = [];

        foreach ($leaveTypes as $leaveType) {
            $taken = Leave::where('staff_member_id', $staffMember->id)
                ->where('leave_type_id', $leaveType->id)
                ->where('status', 'approved')
                ->whereYear('start_date', $year)
                ->sum('total_leave_days');

            $balance[] = [
                'leave_type' => $leaveType,
                'total_days' => $leaveType->days,
                'taken_days' => $taken,
                'remaining_days' => max(0, $leaveType->days - $taken),
            ];
        }

        return response()->json([
            'data' => [
                'staff_member' => $staffMember,
                'year' => $year,
                'balance' => $balance,
            ],
        ]);
    }
}
