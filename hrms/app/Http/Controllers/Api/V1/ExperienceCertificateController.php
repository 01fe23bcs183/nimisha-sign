<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ExperienceCertificate;
use App\Models\StaffMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ExperienceCertificateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ExperienceCertificate::with('author');

        if ($request->has('lang')) {
            $query->where('lang', $request->lang);
        }

        $certificates = $request->has('per_page')
            ? $query->paginate($request->per_page)
            : $query->get();

        return response()->json([
            'data' => $certificates,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lang' => ['required', 'string', 'max:10'],
            'content' => ['required', 'string'],
        ]);

        $validated['author_id'] = $request->user()->id;

        $certificate = ExperienceCertificate::create($validated);

        return response()->json([
            'message' => 'Experience certificate template created successfully',
            'data' => $certificate,
        ], 201);
    }

    public function show(ExperienceCertificate $experienceCertificate): JsonResponse
    {
        return response()->json([
            'data' => $experienceCertificate->load('author'),
        ]);
    }

    public function update(Request $request, ExperienceCertificate $experienceCertificate): JsonResponse
    {
        $validated = $request->validate([
            'lang' => ['sometimes', 'string', 'max:10'],
            'content' => ['sometimes', 'string'],
        ]);

        $experienceCertificate->update($validated);

        return response()->json([
            'message' => 'Experience certificate template updated successfully',
            'data' => $experienceCertificate->fresh(),
        ]);
    }

    public function destroy(ExperienceCertificate $experienceCertificate): JsonResponse
    {
        $experienceCertificate->delete();

        return response()->json([
            'message' => 'Experience certificate template deleted successfully',
        ]);
    }

    public function generate(Request $request, ExperienceCertificate $experienceCertificate, StaffMember $staffMember): JsonResponse
    {
        $content = $experienceCertificate->generateForStaffMember($staffMember);

        return response()->json([
            'data' => [
                'staff_member' => $staffMember,
                'content' => $content,
            ],
        ]);
    }

    public function pdf(Request $request, ExperienceCertificate $experienceCertificate, StaffMember $staffMember)
    {
        $content = $experienceCertificate->generateForStaffMember($staffMember);

        $pdf = Pdf::loadView('letters.experience', [
            'content' => $content,
            'staffMember' => $staffMember,
        ]);

        return $pdf->download('experience-certificate-' . $staffMember->staff_code . '.pdf');
    }
}
