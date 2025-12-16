<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Document::with(['documentType', 'author']);

        if ($request->has('document_type_id')) {
            $query->where('document_type_id', $request->document_type_id);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $documents = $request->has('per_page')
            ? $query->paginate($request->per_page)
            : $query->get();

        return response()->json([
            'data' => $documents,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'document_type_id' => ['required', 'exists:document_types,id'],
            'document' => ['required', 'file', 'max:10240'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $documentPath = $request->file('document')->store('documents', 'public');

        $document = Document::create([
            'name' => $validated['name'],
            'document_type_id' => $validated['document_type_id'],
            'document' => $documentPath,
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'author_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Document created successfully',
            'data' => $document->load('documentType'),
        ], 201);
    }

    public function show(Document $document): JsonResponse
    {
        return response()->json([
            'data' => $document->load(['documentType', 'author']),
        ]);
    }

    public function update(Request $request, Document $document): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'document_type_id' => ['sometimes', 'exists:document_types,id'],
            'document' => ['nullable', 'file', 'max:10240'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        if ($request->hasFile('document')) {
            Storage::disk('public')->delete($document->document);
            $validated['document'] = $request->file('document')->store('documents', 'public');
        }

        $document->update($validated);

        return response()->json([
            'message' => 'Document updated successfully',
            'data' => $document->fresh()->load('documentType'),
        ]);
    }

    public function destroy(Document $document): JsonResponse
    {
        Storage::disk('public')->delete($document->document);
        $document->delete();

        return response()->json([
            'message' => 'Document deleted successfully',
        ]);
    }

    public function download(Document $document)
    {
        if (!Storage::disk('public')->exists($document->document)) {
            return response()->json([
                'message' => 'Document not found',
            ], 404);
        }

        return Storage::disk('public')->download($document->document);
    }
}
