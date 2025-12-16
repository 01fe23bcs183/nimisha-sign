<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\JobTitle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobTitleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = JobTitle::with(['author', 'division', 'division.officeLocation']);

        if ($request->has('division_id')) {
            $query->where('division_id', $request->division_id);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $jobTitles = $request->has('per_page')
            ? $query->paginate($request->per_page)
            : $query->get();

        return response()->json([
            'data' => $jobTitles,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'division_id' => ['required', 'exists:divisions,id'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $validated['author_id'] = $request->user()->id;

        $jobTitle = JobTitle::create($validated);

        return response()->json([
            'message' => 'Job title created successfully',
            'data' => $jobTitle->load('division'),
        ], 201);
    }

    public function show(JobTitle $jobTitle): JsonResponse
    {
        return response()->json([
            'data' => $jobTitle->load('author', 'division', 'division.officeLocation'),
        ]);
    }

    public function update(Request $request, JobTitle $jobTitle): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'division_id' => ['sometimes', 'exists:divisions,id'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $jobTitle->update($validated);

        return response()->json([
            'message' => 'Job title updated successfully',
            'data' => $jobTitle->fresh()->load('division'),
        ]);
    }

    public function destroy(JobTitle $jobTitle): JsonResponse
    {
        $jobTitle->delete();

        return response()->json([
            'message' => 'Job title deleted successfully',
        ]);
    }

    public function fetchByDivision(Request $request): JsonResponse
    {
        $request->validate([
            'division_id' => ['required', 'exists:divisions,id'],
        ]);

        $jobTitles = JobTitle::where('division_id', $request->division_id)
            ->where('is_active', true)
            ->get();

        return response()->json([
            'data' => $jobTitles,
        ]);
    }
}
