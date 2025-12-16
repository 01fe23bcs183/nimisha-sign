<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\JoiningLetter;
use App\Models\StaffMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class JoiningLetterController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = JoiningLetter::with('author');

        if ($request->has('lang')) {
            $query->where('lang', $request->lang);
        }

        $letters = $request->has('per_page')
            ? $query->paginate($request->per_page)
            : $query->get();

        return response()->json([
            'data' => $letters,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lang' => ['required', 'string', 'max:10'],
            'content' => ['required', 'string'],
        ]);

        $validated['author_id'] = $request->user()->id;

        $letter = JoiningLetter::create($validated);

        return response()->json([
            'message' => 'Joining letter template created successfully',
            'data' => $letter,
        ], 201);
    }

    public function show(JoiningLetter $joiningLetter): JsonResponse
    {
        return response()->json([
            'data' => $joiningLetter->load('author'),
        ]);
    }

    public function update(Request $request, JoiningLetter $joiningLetter): JsonResponse
    {
        $validated = $request->validate([
            'lang' => ['sometimes', 'string', 'max:10'],
            'content' => ['sometimes', 'string'],
        ]);

        $joiningLetter->update($validated);

        return response()->json([
            'message' => 'Joining letter template updated successfully',
            'data' => $joiningLetter->fresh(),
        ]);
    }

    public function destroy(JoiningLetter $joiningLetter): JsonResponse
    {
        $joiningLetter->delete();

        return response()->json([
            'message' => 'Joining letter template deleted successfully',
        ]);
    }

    public function generate(Request $request, JoiningLetter $joiningLetter, StaffMember $staffMember): JsonResponse
    {
        $content = $joiningLetter->generateForStaffMember($staffMember);

        return response()->json([
            'data' => [
                'staff_member' => $staffMember,
                'content' => $content,
            ],
        ]);
    }

    public function pdf(Request $request, JoiningLetter $joiningLetter, StaffMember $staffMember)
    {
        $content = $joiningLetter->generateForStaffMember($staffMember);

        $pdf = Pdf::loadView('letters.joining', [
            'content' => $content,
            'staffMember' => $staffMember,
        ]);

        return $pdf->download('joining-letter-' . $staffMember->staff_code . '.pdf');
    }
}
