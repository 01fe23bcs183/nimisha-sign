<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\RecognitionRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecognitionRecordController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = RecognitionRecord::with(['staffMember', 'recognitionCategory', 'author']);

        if ($request->has('staff_member_id')) {
            $query->where('staff_member_id', $request->staff_member_id);
        }

        if ($request->has('recognition_category_id')) {
            $query->where('recognition_category_id', $request->recognition_category_id);
        }

        if ($request->has('date_from')) {
            $query->where('recognition_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('recognition_date', '<=', $request->date_to);
        }

        $records = $request->has('per_page')
            ? $query->orderBy('recognition_date', 'desc')->paginate($request->per_page)
            : $query->orderBy('recognition_date', 'desc')->get();

        return response()->json([
            'data' => $records,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'staff_member_id' => ['required', 'exists:staff_members,id'],
            'recognition_category_id' => ['required', 'exists:recognition_categories,id'],
            'recognition_date' => ['required', 'date'],
            'reward' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['author_id'] = $request->user()->id;

        $record = RecognitionRecord::create($validated);

        return response()->json([
            'message' => 'Recognition record created successfully',
            'data' => $record->load(['staffMember', 'recognitionCategory']),
        ], 201);
    }

    public function show(RecognitionRecord $recognitionRecord): JsonResponse
    {
        return response()->json([
            'data' => $recognitionRecord->load(['staffMember', 'recognitionCategory', 'author']),
        ]);
    }

    public function update(Request $request, RecognitionRecord $recognitionRecord): JsonResponse
    {
        $validated = $request->validate([
            'staff_member_id' => ['sometimes', 'exists:staff_members,id'],
            'recognition_category_id' => ['sometimes', 'exists:recognition_categories,id'],
            'recognition_date' => ['sometimes', 'date'],
            'reward' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $recognitionRecord->update($validated);

        return response()->json([
            'message' => 'Recognition record updated successfully',
            'data' => $recognitionRecord->fresh()->load(['staffMember', 'recognitionCategory']),
        ]);
    }

    public function destroy(RecognitionRecord $recognitionRecord): JsonResponse
    {
        $recognitionRecord->delete();

        return response()->json([
            'message' => 'Recognition record deleted successfully',
        ]);
    }
}
