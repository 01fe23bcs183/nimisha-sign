<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CompanyPolicy;
use App\Models\PolicyAcknowledgment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanyPolicyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = CompanyPolicy::with('author');

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $policies = $request->has('per_page')
            ? $query->paginate($request->per_page)
            : $query->get();

        return response()->json([
            'data' => $policies,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'document' => ['required', 'file', 'max:10240'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $documentPath = $request->file('document')->store('company-policies', 'public');

        $policy = CompanyPolicy::create([
            'name' => $validated['name'],
            'document' => $documentPath,
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'author_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Company policy created successfully',
            'data' => $policy,
        ], 201);
    }

    public function show(CompanyPolicy $companyPolicy): JsonResponse
    {
        return response()->json([
            'data' => $companyPolicy->load(['author', 'acknowledgments.staffMember']),
        ]);
    }

    public function update(Request $request, CompanyPolicy $companyPolicy): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'document' => ['nullable', 'file', 'max:10240'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        if ($request->hasFile('document')) {
            Storage::disk('public')->delete($companyPolicy->document);
            $validated['document'] = $request->file('document')->store('company-policies', 'public');
        }

        $companyPolicy->update($validated);

        return response()->json([
            'message' => 'Company policy updated successfully',
            'data' => $companyPolicy->fresh(),
        ]);
    }

    public function destroy(CompanyPolicy $companyPolicy): JsonResponse
    {
        Storage::disk('public')->delete($companyPolicy->document);
        $companyPolicy->acknowledgments()->delete();
        $companyPolicy->delete();

        return response()->json([
            'message' => 'Company policy deleted successfully',
        ]);
    }

    public function acknowledge(Request $request, CompanyPolicy $companyPolicy): JsonResponse
    {
        $validated = $request->validate([
            'staff_member_id' => ['required', 'exists:staff_members,id'],
        ]);

        PolicyAcknowledgment::updateOrCreate(
            [
                'company_policy_id' => $companyPolicy->id,
                'staff_member_id' => $validated['staff_member_id'],
            ],
            [
                'is_acknowledged' => true,
                'acknowledged_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'Policy acknowledged successfully',
        ]);
    }

    public function download(CompanyPolicy $companyPolicy)
    {
        if (!Storage::disk('public')->exists($companyPolicy->document)) {
            return response()->json([
                'message' => 'Document not found',
            ], 404);
        }

        return Storage::disk('public')->download($companyPolicy->document);
    }
}
