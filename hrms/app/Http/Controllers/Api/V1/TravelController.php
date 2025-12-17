<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Travel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TravelController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Travel::with(['staffMember', 'author']);

        if ($request->has('staff_member_id')) {
            $query->where('staff_member_id', $request->staff_member_id);
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

        $travels = $request->has('per_page')
            ? $query->orderBy('start_date', 'desc')->paginate($request->per_page)
            : $query->orderBy('start_date', 'desc')->get();

        return response()->json([
            'data' => $travels,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'staff_member_id' => ['required', 'exists:staff_members,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'purpose' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['status'] = 'pending';
        $validated['author_id'] = $request->user()->id;

        $travel = Travel::create($validated);

        return response()->json([
            'message' => 'Travel request created successfully',
            'data' => $travel->load('staffMember'),
        ], 201);
    }

    public function show(Travel $travel): JsonResponse
    {
        return response()->json([
            'data' => $travel->load(['staffMember', 'author']),
        ]);
    }

    public function update(Request $request, Travel $travel): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date'],
            'purpose' => ['sometimes', 'string', 'max:255'],
            'destination' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $travel->update($validated);

        return response()->json([
            'message' => 'Travel updated successfully',
            'data' => $travel->fresh()->load('staffMember'),
        ]);
    }

    public function approve(Travel $travel): JsonResponse
    {
        $travel->update(['status' => 'approved']);

        return response()->json([
            'message' => 'Travel approved successfully',
            'data' => $travel->fresh()->load('staffMember'),
        ]);
    }

    public function reject(Travel $travel): JsonResponse
    {
        $travel->update(['status' => 'rejected']);

        return response()->json([
            'message' => 'Travel rejected',
            'data' => $travel->fresh()->load('staffMember'),
        ]);
    }

    public function destroy(Travel $travel): JsonResponse
    {
        $travel->delete();

        return response()->json([
            'message' => 'Travel deleted successfully',
        ]);
    }
}
