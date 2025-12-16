<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentTypeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = DocumentType::with('author');

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $documentTypes = $request->has('per_page')
            ? $query->paginate($request->per_page)
            : $query->get();

        return response()->json([
            'data' => $documentTypes,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $validated['author_id'] = $request->user()->id;

        $documentType = DocumentType::create($validated);

        return response()->json([
            'message' => 'Document type created successfully',
            'data' => $documentType,
        ], 201);
    }

    public function show(DocumentType $documentType): JsonResponse
    {
        return response()->json([
            'data' => $documentType->load('author'),
        ]);
    }

    public function update(Request $request, DocumentType $documentType): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $documentType->update($validated);

        return response()->json([
            'message' => 'Document type updated successfully',
            'data' => $documentType->fresh(),
        ]);
    }

    public function destroy(DocumentType $documentType): JsonResponse
    {
        $documentType->delete();

        return response()->json([
            'message' => 'Document type deleted successfully',
        ]);
    }
}
