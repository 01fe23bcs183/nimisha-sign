<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Division;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Division::with(['author', 'officeLocation']);

        if ($request->has('office_location_id')) {
            $query->where('office_location_id', $request->office_location_id);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $divisions = $request->has('per_page')
            ? $query->paginate($request->per_page)
            : $query->get();

        return response()->json([
            'data' => $divisions,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'office_location_id' => ['required', 'exists:office_locations,id'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $validated['author_id'] = $request->user()->id;

        $division = Division::create($validated);

        return response()->json([
            'message' => 'Division created successfully',
            'data' => $division->load('officeLocation'),
        ], 201);
    }

    public function show(Division $division): JsonResponse
    {
        return response()->json([
            'data' => $division->load('author', 'officeLocation', 'jobTitles'),
        ]);
    }

    public function update(Request $request, Division $division): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'office_location_id' => ['sometimes', 'exists:office_locations,id'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $division->update($validated);

        return response()->json([
            'message' => 'Division updated successfully',
            'data' => $division->fresh()->load('officeLocation'),
        ]);
    }

    public function destroy(Division $division): JsonResponse
    {
        $division->delete();

        return response()->json([
            'message' => 'Division deleted successfully',
        ]);
    }

    public function fetchByLocation(Request $request): JsonResponse
    {
        $request->validate([
            'office_location_id' => ['required', 'exists:office_locations,id'],
        ]);

        $divisions = Division::where('office_location_id', $request->office_location_id)
            ->where('is_active', true)
            ->get();

        return response()->json([
            'data' => $divisions,
        ]);
    }
}
