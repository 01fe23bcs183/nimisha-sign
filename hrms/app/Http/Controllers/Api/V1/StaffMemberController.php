<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\StaffMember;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class StaffMemberController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = StaffMember::with(['user', 'officeLocation', 'division', 'jobTitle', 'author']);

        if ($request->has('office_location_id')) {
            $query->where('office_location_id', $request->office_location_id);
        }

        if ($request->has('division_id')) {
            $query->where('division_id', $request->division_id);
        }

        if ($request->has('job_title_id')) {
            $query->where('job_title_id', $request->job_title_id);
        }

        if ($request->has('employment_status')) {
            $query->where('employment_status', $request->employment_status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('staff_code', 'like', "%{$search}%")
                    ->orWhere('personal_email', 'like', "%{$search}%");
            });
        }

        $staffMembers = $request->has('per_page')
            ? $query->paginate($request->per_page)
            : $query->get();

        return response()->json([
            'data' => $staffMembers,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'personal_email' => ['required', 'email', 'max:255'],
            'mobile_number' => ['nullable', 'string', 'max:50'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:male,female,other'],
            'home_address' => ['nullable', 'string'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'passport_number' => ['nullable', 'string', 'max:50'],
            'country_code' => ['nullable', 'string', 'max:10'],
            'region' => ['nullable', 'string', 'max:100'],
            'city_name' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'staff_code' => ['nullable', 'string', 'max:50', 'unique:staff_members'],
            'biometric_id' => ['nullable', 'string', 'max:50'],
            'office_location_id' => ['required', 'exists:office_locations,id'],
            'division_id' => ['required', 'exists:divisions,id'],
            'job_title_id' => ['required', 'exists:job_titles,id'],
            'hire_date' => ['required', 'date'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_branch' => ['nullable', 'string', 'max:255'],
            'compensation_type' => ['nullable', 'in:monthly,hourly,daily'],
            'base_salary' => ['nullable', 'numeric', 'min:0'],
            'employment_status' => ['nullable', 'in:active,inactive,probation,terminated'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
            'emergency_contact_relation' => ['nullable', 'string', 'max:100'],
            'marital_status' => ['nullable', 'in:single,married,divorced,widowed'],
            'dependents_count' => ['nullable', 'integer', 'min:0'],
            'tax_identification_number' => ['nullable', 'string', 'max:50'],
            'social_security_number' => ['nullable', 'string', 'max:50'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
            'create_user_account' => ['boolean'],
            'password' => ['required_if:create_user_account,true', 'string', 'min:8'],
            'role' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $user = null;

            if ($request->boolean('create_user_account')) {
                $user = User::create([
                    'name' => $validated['full_name'],
                    'email' => $validated['personal_email'],
                    'password' => Hash::make($validated['password']),
                    'is_active' => true,
                ]);

                $user->assignRole($validated['role'] ?? 'staff_member');
            }

            $profilePhotoPath = null;
            if ($request->hasFile('profile_photo')) {
                $profilePhotoPath = $request->file('profile_photo')->store('staff-photos', 'public');
            }

            $staffMember = StaffMember::create([
                ...$validated,
                'user_id' => $user?->id,
                'profile_photo' => $profilePhotoPath,
                'author_id' => $request->user()->id,
            ]);

            return response()->json([
                'message' => 'Staff member created successfully',
                'data' => $staffMember->load(['user', 'officeLocation', 'division', 'jobTitle']),
            ], 201);
        });
    }

    public function show(StaffMember $staffMember): JsonResponse
    {
        return response()->json([
            'data' => $staffMember->load([
                'user',
                'officeLocation',
                'division',
                'jobTitle',
                'author',
                'staffFiles.fileCategory',
                'recognitionRecords.recognitionCategory',
                'roleUpgrades.newJobTitle',
                'locationTransfers',
                'disciplineNotes',
                'leaves.leaveType',
                'attendances',
            ]),
        ]);
    }

    public function update(Request $request, StaffMember $staffMember): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => ['sometimes', 'string', 'max:255'],
            'personal_email' => ['sometimes', 'email', 'max:255'],
            'mobile_number' => ['nullable', 'string', 'max:50'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:male,female,other'],
            'home_address' => ['nullable', 'string'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'passport_number' => ['nullable', 'string', 'max:50'],
            'country_code' => ['nullable', 'string', 'max:10'],
            'region' => ['nullable', 'string', 'max:100'],
            'city_name' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'staff_code' => ['nullable', 'string', 'max:50', 'unique:staff_members,staff_code,' . $staffMember->id],
            'biometric_id' => ['nullable', 'string', 'max:50'],
            'office_location_id' => ['sometimes', 'exists:office_locations,id'],
            'division_id' => ['sometimes', 'exists:divisions,id'],
            'job_title_id' => ['sometimes', 'exists:job_titles,id'],
            'hire_date' => ['sometimes', 'date'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_branch' => ['nullable', 'string', 'max:255'],
            'compensation_type' => ['nullable', 'in:monthly,hourly,daily'],
            'base_salary' => ['nullable', 'numeric', 'min:0'],
            'employment_status' => ['nullable', 'in:active,inactive,probation,terminated'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
            'emergency_contact_relation' => ['nullable', 'string', 'max:100'],
            'marital_status' => ['nullable', 'in:single,married,divorced,widowed'],
            'dependents_count' => ['nullable', 'integer', 'min:0'],
            'tax_identification_number' => ['nullable', 'string', 'max:50'],
            'social_security_number' => ['nullable', 'string', 'max:50'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('profile_photo')) {
            if ($staffMember->profile_photo) {
                Storage::disk('public')->delete($staffMember->profile_photo);
            }
            $validated['profile_photo'] = $request->file('profile_photo')->store('staff-photos', 'public');
        }

        $staffMember->update($validated);

        if ($staffMember->user && isset($validated['full_name'])) {
            $staffMember->user->update(['name' => $validated['full_name']]);
        }

        return response()->json([
            'message' => 'Staff member updated successfully',
            'data' => $staffMember->fresh()->load(['user', 'officeLocation', 'division', 'jobTitle']),
        ]);
    }

    public function destroy(StaffMember $staffMember): JsonResponse
    {
        $staffMember->delete();

        return response()->json([
            'message' => 'Staff member deleted successfully',
        ]);
    }

    public function salary(StaffMember $staffMember): JsonResponse
    {
        $staffMember->load([
            'allowances.allowanceOption',
            'commissions',
            'loans.loanOption',
            'saturationDeductions.deductionOption',
            'otherPayments',
            'overtimes',
            'companyContributions',
        ]);

        return response()->json([
            'data' => [
                'staff_member' => $staffMember,
                'base_salary' => $staffMember->base_salary,
                'net_salary' => $staffMember->getNetSalary(),
            ],
        ]);
    }

    public function updateSalary(Request $request, StaffMember $staffMember): JsonResponse
    {
        $validated = $request->validate([
            'base_salary' => ['required', 'numeric', 'min:0'],
            'compensation_type' => ['nullable', 'in:monthly,hourly,daily'],
        ]);

        $staffMember->update($validated);

        return response()->json([
            'message' => 'Salary updated successfully',
            'data' => $staffMember->fresh(),
        ]);
    }
}
