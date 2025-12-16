<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Event::with(['author', 'staffMembers']);

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('end_date', '<=', $request->date_to);
        }

        if ($request->has('month')) {
            $query->whereMonth('start_date', $request->month);
        }

        if ($request->has('year')) {
            $query->whereYear('start_date', $request->year);
        }

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $events = $request->has('per_page')
            ? $query->orderBy('start_date', 'asc')->paginate($request->per_page)
            : $query->orderBy('start_date', 'asc')->get();

        return response()->json([
            'data' => $events,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'color' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'staff_member_ids' => ['nullable', 'array'],
            'staff_member_ids.*' => ['exists:staff_members,id'],
        ]);

        $validated['author_id'] = $request->user()->id;

        $event = Event::create($validated);

        if (!empty($validated['staff_member_ids'])) {
            $event->staffMembers()->attach($validated['staff_member_ids']);
        }

        return response()->json([
            'message' => 'Event created successfully',
            'data' => $event->load('staffMembers'),
        ], 201);
    }

    public function show(Event $event): JsonResponse
    {
        return response()->json([
            'data' => $event->load(['author', 'staffMembers']),
        ]);
    }

    public function update(Request $request, Event $event): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date'],
            'color' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'staff_member_ids' => ['nullable', 'array'],
            'staff_member_ids.*' => ['exists:staff_members,id'],
        ]);

        $event->update($validated);

        if (isset($validated['staff_member_ids'])) {
            $event->staffMembers()->sync($validated['staff_member_ids']);
        }

        return response()->json([
            'message' => 'Event updated successfully',
            'data' => $event->fresh()->load('staffMembers'),
        ]);
    }

    public function destroy(Event $event): JsonResponse
    {
        $event->staffMembers()->detach();
        $event->delete();

        return response()->json([
            'message' => 'Event deleted successfully',
        ]);
    }

    public function calendar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer'],
        ]);

        $events = Event::where('is_active', true)
            ->where(function ($query) use ($validated) {
                $startOfMonth = "{$validated['year']}-{$validated['month']}-01";
                $endOfMonth = date('Y-m-t', strtotime($startOfMonth));
                
                $query->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                    ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth])
                    ->orWhere(function ($q) use ($startOfMonth, $endOfMonth) {
                        $q->where('start_date', '<=', $startOfMonth)
                            ->where('end_date', '>=', $endOfMonth);
                    });
            })
            ->get();

        return response()->json([
            'data' => $events,
        ]);
    }
}
