<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\IpRestrict;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IpRestrictController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = IpRestrict::with('author');

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('search')) {
            $query->where('ip_address', 'like', '%' . $request->search . '%');
        }

        $ipRestricts = $request->has('per_page')
            ? $query->paginate($request->per_page)
            : $query->get();

        return response()->json([
            'data' => $ipRestricts,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ip_address' => ['required', 'ip', 'unique:ip_restricts,ip_address'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $validated['author_id'] = $request->user()->id;

        $ipRestrict = IpRestrict::create($validated);

        return response()->json([
            'message' => 'IP restriction created successfully',
            'data' => $ipRestrict,
        ], 201);
    }

    public function show(IpRestrict $ipRestrict): JsonResponse
    {
        return response()->json([
            'data' => $ipRestrict->load('author'),
        ]);
    }

    public function update(Request $request, IpRestrict $ipRestrict): JsonResponse
    {
        $validated = $request->validate([
            'ip_address' => ['sometimes', 'ip', 'unique:ip_restricts,ip_address,' . $ipRestrict->id],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $ipRestrict->update($validated);

        return response()->json([
            'message' => 'IP restriction updated successfully',
            'data' => $ipRestrict->fresh(),
        ]);
    }

    public function destroy(IpRestrict $ipRestrict): JsonResponse
    {
        $ipRestrict->delete();

        return response()->json([
            'message' => 'IP restriction deleted successfully',
        ]);
    }

    public function checkIp(Request $request): JsonResponse
    {
        $ip = $request->ip();
        $isAllowed = IpRestrict::isIpAllowed($ip);

        return response()->json([
            'data' => [
                'ip' => $ip,
                'is_allowed' => $isAllowed,
            ],
        ]);
    }
}
