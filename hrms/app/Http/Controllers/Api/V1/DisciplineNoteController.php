<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DisciplineNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DisciplineNoteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = DisciplineNote::with(['staffMember', 'issuedToUser', 'author']);

        if ($request->has('staff_member_id')) {
            $query->where('staff_member_id', $request->staff_member_id);
        }

        if ($request->has('date_from')) {
            $query->where('issue_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('issue_date', '<=', $request->date_to);
        }

        $notes = $request->has('per_page')
            ? $query->orderBy('issue_date', 'desc')->paginate($request->per_page)
            : $query->orderBy('issue_date', 'desc')->get();

        return response()->json([
            'data' => $notes,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'staff_member_id' => ['required', 'exists:staff_members,id'],
            'issued_to_user_id' => ['nullable', 'exists:users,id'],
            'subject' => ['required', 'string', 'max:255'],
            'issue_date' => ['required', 'date'],
            'details' => ['nullable', 'string'],
        ]);

        $validated['author_id'] = $request->user()->id;

        $note = DisciplineNote::create($validated);

        return response()->json([
            'message' => 'Discipline note created successfully',
            'data' => $note->load(['staffMember', 'issuedToUser']),
        ], 201);
    }

    public function show(DisciplineNote $disciplineNote): JsonResponse
    {
        return response()->json([
            'data' => $disciplineNote->load(['staffMember', 'issuedToUser', 'author']),
        ]);
    }

    public function update(Request $request, DisciplineNote $disciplineNote): JsonResponse
    {
        $validated = $request->validate([
            'subject' => ['sometimes', 'string', 'max:255'],
            'issue_date' => ['sometimes', 'date'],
            'details' => ['nullable', 'string'],
        ]);

        $disciplineNote->update($validated);

        return response()->json([
            'message' => 'Discipline note updated successfully',
            'data' => $disciplineNote->fresh()->load(['staffMember', 'issuedToUser']),
        ]);
    }

    public function destroy(DisciplineNote $disciplineNote): JsonResponse
    {
        $disciplineNote->delete();

        return response()->json([
            'message' => 'Discipline note deleted successfully',
        ]);
    }
}
