<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LocationTransfer;
use App\Models\StaffMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocationTransferController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = LocationTransfer::with(['staffMember', 'newOfficeLocation', 'newDivision', 'author']);

        if ($request->has('staff_member_id')) {
            $query->where('staff_member_id', $request->staff_member_id);
        }

        if ($request->has('date_from')) {
            $query->where('effective_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('effective_date', '<=', $request->date_to);
        }

        $transfers = $request->has('per_page')
            ? $query->orderBy('effective_date', 'desc')->paginate($request->per_page)
            : $query->orderBy('effective_date', 'desc')->get();

        return response()->json([
            'data' => $transfers,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'staff_member_id' => ['required', 'exists:staff_members,id'],
            'new_office_location_id' => ['required', 'exists:office_locations,id'],
            'new_division_id' => ['required', 'exists:divisions,id'],
            'effective_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['author_id'] = $request->user()->id;

        return DB::transaction(function () use ($validated) {
            $transfer = LocationTransfer::create($validated);

            StaffMember::where('id', $validated['staff_member_id'])
                ->update([
                    'office_location_id' => $validated['new_office_location_id'],
                    'division_id' => $validated['new_division_id'],
                ]);

            return response()->json([
                'message' => 'Location transfer created successfully',
                'data' => $transfer->load(['staffMember', 'newOfficeLocation', 'newDivision']),
            ], 201);
        });
    }

    public function show(LocationTransfer $locationTransfer): JsonResponse
    {
        return response()->json([
            'data' => $locationTransfer->load(['staffMember', 'newOfficeLocation', 'newDivision', 'author']),
        ]);
    }

    public function update(Request $request, LocationTransfer $locationTransfer): JsonResponse
    {
        $validated = $request->validate([
            'effective_date' => ['sometimes', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $locationTransfer->update($validated);

        return response()->json([
            'message' => 'Location transfer updated successfully',
            'data' => $locationTransfer->fresh()->load(['staffMember', 'newOfficeLocation', 'newDivision']),
        ]);
    }

    public function destroy(LocationTransfer $locationTransfer): JsonResponse
    {
        $locationTransfer->delete();

        return response()->json([
            'message' => 'Location transfer deleted successfully',
        ]);
    }
}
