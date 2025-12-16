<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\StaffMember;
use App\Models\VoluntaryExit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoluntaryExitController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = VoluntaryExit::with(['staffMember', 'author']);

        if ($request->has('staff_member_id')) {
            $query->where('staff_member_id', $request->staff_member_id);
        }

        if ($request->has('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }

        if ($request->has('date_from')) {
            $query->where('notice_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('notice_date', '<=', $request->date_to);
        }

        $exits = $request->has('per_page')
            ? $query->orderBy('notice_date', 'desc')->paginate($request->per_page)
            : $query->orderBy('notice_date', 'desc')->get();

        return response()->json([
            'data' => $exits,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'staff_member_id' => ['required', 'exists:staff_members,id'],
            'notice_date' => ['required', 'date'],
            'exit_date' => ['required', 'date', 'after_or_equal:notice_date'],
            'reason' => ['nullable', 'string'],
        ]);

        $validated['approval_status'] = 'pending';
        $validated['author_id'] = $request->user()->id;

        $exit = VoluntaryExit::create($validated);

        return response()->json([
            'message' => 'Voluntary exit request created successfully',
            'data' => $exit->load('staffMember'),
        ], 201);
    }

    public function show(VoluntaryExit $voluntaryExit): JsonResponse
    {
        return response()->json([
            'data' => $voluntaryExit->load(['staffMember', 'author']),
        ]);
    }

    public function update(Request $request, VoluntaryExit $voluntaryExit): JsonResponse
    {
        $validated = $request->validate([
            'notice_date' => ['sometimes', 'date'],
            'exit_date' => ['sometimes', 'date'],
            'reason' => ['nullable', 'string'],
        ]);

        $voluntaryExit->update($validated);

        return response()->json([
            'message' => 'Voluntary exit updated successfully',
            'data' => $voluntaryExit->fresh()->load('staffMember'),
        ]);
    }

    public function approve(Request $request, VoluntaryExit $voluntaryExit): JsonResponse
    {
        return DB::transaction(function () use ($voluntaryExit) {
            $voluntaryExit->update(['approval_status' => 'approved']);

            StaffMember::where('id', $voluntaryExit->staff_member_id)
                ->update(['employment_status' => 'terminated']);

            return response()->json([
                'message' => 'Voluntary exit approved successfully',
                'data' => $voluntaryExit->fresh()->load('staffMember'),
            ]);
        });
    }

    public function decline(Request $request, VoluntaryExit $voluntaryExit): JsonResponse
    {
        $voluntaryExit->update(['approval_status' => 'declined']);

        return response()->json([
            'message' => 'Voluntary exit declined',
            'data' => $voluntaryExit->fresh()->load('staffMember'),
        ]);
    }

    public function destroy(VoluntaryExit $voluntaryExit): JsonResponse
    {
        $voluntaryExit->delete();

        return response()->json([
            'message' => 'Voluntary exit deleted successfully',
        ]);
    }
}
