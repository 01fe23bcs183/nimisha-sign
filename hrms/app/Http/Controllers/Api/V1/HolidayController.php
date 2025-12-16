<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Holiday::with('author');

        if ($request->has('year')) {
            $query->whereYear('date', $request->year);
        }

        if ($request->has('month')) {
            $query->whereMonth('date', $request->month);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $holidays = $request->has('per_page')
            ? $query->orderBy('date', 'asc')->paginate($request->per_page)
            : $query->orderBy('date', 'asc')->get();

        return response()->json([
            'data' => $holidays,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $validated['author_id'] = $request->user()->id;

        $holiday = Holiday::create($validated);

        return response()->json([
            'message' => 'Holiday created successfully',
            'data' => $holiday,
        ], 201);
    }

    public function show(Holiday $holiday): JsonResponse
    {
        return response()->json([
            'data' => $holiday->load('author'),
        ]);
    }

    public function update(Request $request, Holiday $holiday): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'date' => ['sometimes', 'date'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $holiday->update($validated);

        return response()->json([
            'message' => 'Holiday updated successfully',
            'data' => $holiday->fresh(),
        ]);
    }

    public function destroy(Holiday $holiday): JsonResponse
    {
        $holiday->delete();

        return response()->json([
            'message' => 'Holiday deleted successfully',
        ]);
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'holidays' => ['required', 'array'],
            'holidays.*.name' => ['required', 'string', 'max:255'],
            'holidays.*.date' => ['required', 'date'],
            'holidays.*.description' => ['nullable', 'string'],
        ]);

        $holidays = [];
        foreach ($request->holidays as $holidayData) {
            $holidays[] = Holiday::create([
                ...$holidayData,
                'author_id' => $request->user()->id,
            ]);
        }

        return response()->json([
            'message' => count($holidays) . ' holidays imported successfully',
            'data' => $holidays,
        ], 201);
    }
}
