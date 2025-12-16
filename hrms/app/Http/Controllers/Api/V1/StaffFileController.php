<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\StaffFile;
use App\Models\StaffMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StaffFileController extends Controller
{
    public function index(Request $request, StaffMember $staffMember): JsonResponse
    {
        $files = $staffMember->staffFiles()->with('fileCategory')->get();

        return response()->json([
            'data' => $files,
        ]);
    }

    public function store(Request $request, StaffMember $staffMember): JsonResponse
    {
        $validated = $request->validate([
            'file_category_id' => ['required', 'exists:file_categories,id'],
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $filePath = $request->file('file')->store('staff-files/' . $staffMember->id, 'public');

        $staffFile = $staffMember->staffFiles()->create([
            'file_category_id' => $validated['file_category_id'],
            'file_path' => $filePath,
            'author_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'File uploaded successfully',
            'data' => $staffFile->load('fileCategory'),
        ], 201);
    }

    public function show(StaffMember $staffMember, StaffFile $staffFile): JsonResponse
    {
        return response()->json([
            'data' => $staffFile->load('fileCategory'),
        ]);
    }

    public function destroy(StaffMember $staffMember, StaffFile $staffFile): JsonResponse
    {
        Storage::disk('public')->delete($staffFile->file_path);
        $staffFile->delete();

        return response()->json([
            'message' => 'File deleted successfully',
        ]);
    }

    public function download(StaffMember $staffMember, StaffFile $staffFile)
    {
        if (!Storage::disk('public')->exists($staffFile->file_path)) {
            return response()->json([
                'message' => 'File not found',
            ], 404);
        }

        return Storage::disk('public')->download($staffFile->file_path);
    }
}
