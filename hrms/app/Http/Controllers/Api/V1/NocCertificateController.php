<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\NocCertificate;
use App\Models\StaffMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class NocCertificateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = NocCertificate::with('author');

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

        $certificate = NocCertificate::create($validated);

        return response()->json([
            'message' => 'NOC certificate template created successfully',
            'data' => $certificate,
        ], 201);
    }

    public function show(NocCertificate $nocCertificate): JsonResponse
    {
        return response()->json([
            'data' => $nocCertificate->load('author'),
        ]);
    }

    public function update(Request $request, NocCertificate $nocCertificate): JsonResponse
    {
        $validated = $request->validate([
            'lang' => ['sometimes', 'string', 'max:10'],
            'content' => ['sometimes', 'string'],
        ]);

        $nocCertificate->update($validated);

        return response()->json([
            'message' => 'NOC certificate template updated successfully',
            'data' => $nocCertificate->fresh(),
        ]);
    }

    public function destroy(NocCertificate $nocCertificate): JsonResponse
    {
        $nocCertificate->delete();

        return response()->json([
            'message' => 'NOC certificate template deleted successfully',
        ]);
    }

    public function generate(Request $request, NocCertificate $nocCertificate, StaffMember $staffMember): JsonResponse
    {
        $content = $nocCertificate->generateForStaffMember($staffMember);

        return response()->json([
            'data' => [
                'staff_member' => $staffMember,
                'content' => $content,
            ],
        ]);
    }

    public function pdf(Request $request, NocCertificate $nocCertificate, StaffMember $staffMember)
    {
        $content = $nocCertificate->generateForStaffMember($staffMember);

        $pdf = Pdf::loadView('letters.noc', [
            'content' => $content,
            'staffMember' => $staffMember,
        ]);

        return $pdf->download('noc-certificate-' . $staffMember->staff_code . '.pdf');
    }
}
