<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Offboarding;
use App\Models\StaffMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OffboardingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Offboarding::with(['staffMember', 'exitCategory', 'author']);

        if ($request->has('staff_member_id')) {
            $query->where('staff_member_id', $request->staff_member_id);
        }

        if ($request->has('exit_category_id')) {
            $query->where('exit_category_id', $request->exit_category_id);
        }

        if ($request->has('date_from')) {
            $query->where('exit_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('exit_date', '<=', $request->date_to);
        }

        $offboardings = $request->has('per_page')
            ? $query->orderBy('exit_date', 'desc')->paginate($request->per_page)
            : $query->orderBy('exit_date', 'desc')->get();

        return response()->json([
            'data' => $offboardings,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'staff_member_id' => ['required', 'exists:staff_members,id'],
            'exit_category_id' => ['required', 'exists:exit_categories,id'],
            'exit_date' => ['required', 'date'],
            'notice_date' => ['nullable', 'date'],
            'details' => ['nullable', 'string'],
        ]);

        $validated['author_id'] = $request->user()->id;

        return DB::transaction(function () use ($validated) {
            $offboarding = Offboarding::create($validated);

            StaffMember::where('id', $validated['staff_member_id'])
                ->update(['employment_status' => 'terminated']);

            return response()->json([
                'message' => 'Offboarding created successfully',
                'data' => $offboarding->load(['staffMember', 'exitCategory']),
            ], 201);
        });
    }

    public function show(Offboarding $offboarding): JsonResponse
    {
        return response()->json([
            'data' => $offboarding->load(['staffMember', 'exitCategory', 'author']),
        ]);
    }

    public function update(Request $request, Offboarding $offboarding): JsonResponse
    {
        $validated = $request->validate([
            'exit_category_id' => ['sometimes', 'exists:exit_categories,id'],
            'exit_date' => ['sometimes', 'date'],
            'notice_date' => ['nullable', 'date'],
            'details' => ['nullable', 'string'],
        ]);

        $offboarding->update($validated);

        return response()->json([
            'message' => 'Offboarding updated successfully',
            'data' => $offboarding->fresh()->load(['staffMember', 'exitCategory']),
        ]);
    }

    public function destroy(Offboarding $offboarding): JsonResponse
    {
        $offboarding->delete();

        return response()->json([
            'message' => 'Offboarding deleted successfully',
        ]);
    }
}
