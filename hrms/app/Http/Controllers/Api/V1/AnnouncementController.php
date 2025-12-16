<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Announcement::with(['author', 'staffMembers']);

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('current')) {
            $today = now()->toDateString();
            $query->where('start_date', '<=', $today)
                ->where('end_date', '>=', $today);
        }

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $announcements = $request->has('per_page')
            ? $query->orderBy('start_date', 'desc')->paginate($request->per_page)
            : $query->orderBy('start_date', 'desc')->get();

        return response()->json([
            'data' => $announcements,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'staff_member_ids' => ['nullable', 'array'],
            'staff_member_ids.*' => ['exists:staff_members,id'],
        ]);

        $validated['author_id'] = $request->user()->id;

        $announcement = Announcement::create($validated);

        if (!empty($validated['staff_member_ids'])) {
            $announcement->staffMembers()->attach($validated['staff_member_ids']);
        }

        return response()->json([
            'message' => 'Announcement created successfully',
            'data' => $announcement->load('staffMembers'),
        ], 201);
    }

    public function show(Announcement $announcement): JsonResponse
    {
        return response()->json([
            'data' => $announcement->load(['author', 'staffMembers']),
        ]);
    }

    public function update(Request $request, Announcement $announcement): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'staff_member_ids' => ['nullable', 'array'],
            'staff_member_ids.*' => ['exists:staff_members,id'],
        ]);

        $announcement->update($validated);

        if (isset($validated['staff_member_ids'])) {
            $announcement->staffMembers()->sync($validated['staff_member_ids']);
        }

        return response()->json([
            'message' => 'Announcement updated successfully',
            'data' => $announcement->fresh()->load('staffMembers'),
        ]);
    }

    public function destroy(Announcement $announcement): JsonResponse
    {
        $announcement->staffMembers()->detach();
        $announcement->delete();

        return response()->json([
            'message' => 'Announcement deleted successfully',
        ]);
    }
}
