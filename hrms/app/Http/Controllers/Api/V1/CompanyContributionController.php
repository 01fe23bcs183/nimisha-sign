<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CompanyContribution;
use App\Models\StaffMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyContributionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = CompanyContribution::with(['staffMember', 'author']);

        if ($request->has('staff_member_id')) {
            $query->where('staff_member_id', $request->staff_member_id);
        }

        $contributions = $request->has('per_page')
            ? $query->paginate($request->per_page)
            : $query->get();

        return response()->json([
            'data' => $contributions,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'staff_member_id' => ['required', 'exists:staff_members,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:fixed,percentage'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $validated['author_id'] = $request->user()->id;

        $contribution = CompanyContribution::create($validated);

        return response()->json([
            'message' => 'Company contribution created successfully',
            'data' => $contribution->load('staffMember'),
        ], 201);
    }

    public function show(CompanyContribution $companyContribution): JsonResponse
    {
        return response()->json([
            'data' => $companyContribution->load(['staffMember', 'author']),
        ]);
    }

    public function update(Request $request, CompanyContribution $companyContribution): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'in:fixed,percentage'],
            'amount' => ['sometimes', 'numeric', 'min:0'],
        ]);

        $companyContribution->update($validated);

        return response()->json([
            'message' => 'Company contribution updated successfully',
            'data' => $companyContribution->fresh()->load('staffMember'),
        ]);
    }

    public function destroy(CompanyContribution $companyContribution): JsonResponse
    {
        $companyContribution->delete();

        return response()->json([
            'message' => 'Company contribution deleted successfully',
        ]);
    }

    public function byStaffMember(StaffMember $staffMember): JsonResponse
    {
        $contributions = $staffMember->companyContributions()->get();

        return response()->json([
            'data' => $contributions,
        ]);
    }
}
