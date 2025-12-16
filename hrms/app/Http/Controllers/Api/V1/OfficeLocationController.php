<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\OfficeLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfficeLocationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = OfficeLocation::with('author');

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $officeLocations = $request->has('per_page')
            ? $query->paginate($request->per_page)
            : $query->get();

        return response()->json([
            'data' => $officeLocations,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $validated['author_id'] = $request->user()->id;

        $officeLocation = OfficeLocation::create($validated);

        return response()->json([
            'message' => 'Office location created successfully',
            'data' => $officeLocation,
        ], 201);
    }

    public function show(OfficeLocation $officeLocation): JsonResponse
    {
        return response()->json([
            'data' => $officeLocation->load('author', 'divisions'),
        ]);
    }

    public function update(Request $request, OfficeLocation $officeLocation): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $officeLocation->update($validated);

        return response()->json([
            'message' => 'Office location updated successfully',
            'data' => $officeLocation->fresh(),
        ]);
    }

    public function destroy(OfficeLocation $officeLocation): JsonResponse
    {
        $officeLocation->delete();

        return response()->json([
            'message' => 'Office location deleted successfully',
        ]);
    }
}
