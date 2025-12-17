<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Complaint::with(['complainant', 'complainedAgainst', 'author']);

        if ($request->has('complaint_from')) {
            $query->where('complaint_from', $request->complaint_from);
        }

        if ($request->has('complaint_against')) {
            $query->where('complaint_against', $request->complaint_against);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from')) {
            $query->where('complaint_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('complaint_date', '<=', $request->date_to);
        }

        $complaints = $request->has('per_page')
            ? $query->orderBy('complaint_date', 'desc')->paginate($request->per_page)
            : $query->orderBy('complaint_date', 'desc')->get();

        return response()->json([
            'data' => $complaints,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'complaint_from' => ['required', 'exists:staff_members,id'],
            'complaint_against' => ['nullable', 'exists:staff_members,id'],
            'title' => ['required', 'string', 'max:255'],
            'complaint_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['status'] = 'open';
        $validated['author_id'] = $request->user()->id;

        $complaint = Complaint::create($validated);

        return response()->json([
            'message' => 'Complaint created successfully',
            'data' => $complaint->load(['complainant', 'complainedAgainst']),
        ], 201);
    }

    public function show(Complaint $complaint): JsonResponse
    {
        return response()->json([
            'data' => $complaint->load(['complainant', 'complainedAgainst', 'author']),
        ]);
    }

    public function update(Request $request, Complaint $complaint): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'complaint_date' => ['sometimes', 'date'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:pending,investigating,resolved,dismissed'],
        ]);

        $complaint->update($validated);

        return response()->json([
            'message' => 'Complaint updated successfully',
            'data' => $complaint->fresh()->load(['complainant', 'complainedAgainst']),
        ]);
    }

    public function resolve(Complaint $complaint): JsonResponse
    {
        $complaint->update(['status' => 'resolved']);

        return response()->json([
            'message' => 'Complaint resolved',
            'data' => $complaint->fresh()->load(['complainant', 'complainedAgainst']),
        ]);
    }

    public function destroy(Complaint $complaint): JsonResponse
    {
        $complaint->delete();

        return response()->json([
            'message' => 'Complaint deleted successfully',
        ]);
    }
}
