<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Overtime;
use App\Models\StaffMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OvertimeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Overtime::with(['staffMember', 'author']);

        if ($request->has('staff_member_id')) {
            $query->where('staff_member_id', $request->staff_member_id);
        }

        if ($request->has('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('end_date', '<=', $request->date_to);
        }

        $overtimes = $request->has('per_page')
            ? $query->orderBy('start_date', 'desc')->paginate($request->per_page)
            : $query->orderBy('start_date', 'desc')->get();

        return response()->json([
            'data' => $overtimes,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'staff_member_id' => ['required', 'exists:staff_members,id'],
            'title' => ['required', 'string', 'max:255'],
            'number_of_days' => ['required', 'integer', 'min:1'],
            'hours' => ['required', 'numeric', 'min:0'],
            'rate' => ['required', 'numeric', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $validated['author_id'] = $request->user()->id;

        $overtime = Overtime::create($validated);

        return response()->json([
            'message' => 'Overtime created successfully',
            'data' => $overtime->load('staffMember'),
        ], 201);
    }

    public function show(Overtime $overtime): JsonResponse
    {
        return response()->json([
            'data' => $overtime->load(['staffMember', 'author']),
        ]);
    }

    public function update(Request $request, Overtime $overtime): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'number_of_days' => ['sometimes', 'integer', 'min:1'],
            'hours' => ['sometimes', 'numeric', 'min:0'],
            'rate' => ['sometimes', 'numeric', 'min:0'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date'],
        ]);

        $overtime->update($validated);

        return response()->json([
            'message' => 'Overtime updated successfully',
            'data' => $overtime->fresh()->load('staffMember'),
        ]);
    }

    public function destroy(Overtime $overtime): JsonResponse
    {
        $overtime->delete();

        return response()->json([
            'message' => 'Overtime deleted successfully',
        ]);
    }

    public function byStaffMember(StaffMember $staffMember): JsonResponse
    {
        $overtimes = $staffMember->overtimes()->get();

        return response()->json([
            'data' => $overtimes,
        ]);
    }
}
