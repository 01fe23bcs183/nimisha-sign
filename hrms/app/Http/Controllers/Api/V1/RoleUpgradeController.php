<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\RoleUpgrade;
use App\Models\StaffMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleUpgradeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = RoleUpgrade::with(['staffMember', 'newJobTitle', 'author']);

        if ($request->has('staff_member_id')) {
            $query->where('staff_member_id', $request->staff_member_id);
        }

        if ($request->has('date_from')) {
            $query->where('effective_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('effective_date', '<=', $request->date_to);
        }

        $upgrades = $request->has('per_page')
            ? $query->orderBy('effective_date', 'desc')->paginate($request->per_page)
            : $query->orderBy('effective_date', 'desc')->get();

        return response()->json([
            'data' => $upgrades,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'staff_member_id' => ['required', 'exists:staff_members,id'],
            'new_job_title_id' => ['required', 'exists:job_titles,id'],
            'upgrade_title' => ['required', 'string', 'max:255'],
            'effective_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['author_id'] = $request->user()->id;

        return DB::transaction(function () use ($validated) {
            $upgrade = RoleUpgrade::create($validated);

            StaffMember::where('id', $validated['staff_member_id'])
                ->update(['job_title_id' => $validated['new_job_title_id']]);

            return response()->json([
                'message' => 'Role upgrade created successfully',
                'data' => $upgrade->load(['staffMember', 'newJobTitle']),
            ], 201);
        });
    }

    public function show(RoleUpgrade $roleUpgrade): JsonResponse
    {
        return response()->json([
            'data' => $roleUpgrade->load(['staffMember', 'newJobTitle', 'author']),
        ]);
    }

    public function update(Request $request, RoleUpgrade $roleUpgrade): JsonResponse
    {
        $validated = $request->validate([
            'upgrade_title' => ['sometimes', 'string', 'max:255'],
            'effective_date' => ['sometimes', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $roleUpgrade->update($validated);

        return response()->json([
            'message' => 'Role upgrade updated successfully',
            'data' => $roleUpgrade->fresh()->load(['staffMember', 'newJobTitle']),
        ]);
    }

    public function destroy(RoleUpgrade $roleUpgrade): JsonResponse
    {
        $roleUpgrade->delete();

        return response()->json([
            'message' => 'Role upgrade deleted successfully',
        ]);
    }
}
