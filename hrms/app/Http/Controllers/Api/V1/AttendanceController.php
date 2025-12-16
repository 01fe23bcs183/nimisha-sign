<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\CompanySetting;
use App\Models\IpRestrict;
use App\Models\StaffMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Attendance::with(['staffMember', 'author']);

        if ($request->has('staff_member_id')) {
            $query->where('staff_member_id', $request->staff_member_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->has('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        if ($request->has('month')) {
            $query->whereMonth('date', $request->month);
        }

        if ($request->has('year')) {
            $query->whereYear('date', $request->year);
        }

        $attendances = $request->has('per_page')
            ? $query->orderBy('date', 'desc')->paginate($request->per_page)
            : $query->orderBy('date', 'desc')->get();

        return response()->json([
            'data' => $attendances,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'staff_member_id' => ['required', 'exists:staff_members,id'],
            'date' => ['required', 'date'],
            'status' => ['required', 'in:present,absent,half_day,late'],
            'clock_in' => ['nullable', 'date_format:H:i:s'],
            'clock_out' => ['nullable', 'date_format:H:i:s'],
            'late_minutes' => ['nullable', 'integer', 'min:0'],
            'early_leaving_minutes' => ['nullable', 'integer', 'min:0'],
            'overtime_minutes' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['author_id'] = $request->user()->id;

        $attendance = Attendance::updateOrCreate(
            [
                'staff_member_id' => $validated['staff_member_id'],
                'date' => $validated['date'],
            ],
            $validated
        );

        return response()->json([
            'message' => 'Attendance recorded successfully',
            'data' => $attendance->load('staffMember'),
        ], 201);
    }

    public function show(Attendance $attendance): JsonResponse
    {
        return response()->json([
            'data' => $attendance->load(['staffMember', 'author']),
        ]);
    }

    public function update(Request $request, Attendance $attendance): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['sometimes', 'in:present,absent,half_day,late'],
            'clock_in' => ['nullable', 'date_format:H:i:s'],
            'clock_out' => ['nullable', 'date_format:H:i:s'],
            'late_minutes' => ['nullable', 'integer', 'min:0'],
            'early_leaving_minutes' => ['nullable', 'integer', 'min:0'],
            'overtime_minutes' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $attendance->update($validated);

        return response()->json([
            'message' => 'Attendance updated successfully',
            'data' => $attendance->fresh()->load('staffMember'),
        ]);
    }

    public function destroy(Attendance $attendance): JsonResponse
    {
        $attendance->delete();

        return response()->json([
            'message' => 'Attendance deleted successfully',
        ]);
    }

    public function clockIn(Request $request): JsonResponse
    {
        $ipRestrict = CompanySetting::getValue('ip_restrict', false);
        if ($ipRestrict && !IpRestrict::isIpAllowed($request->ip())) {
            return response()->json([
                'message' => 'Clock-in not allowed from this IP address',
            ], 403);
        }

        $staffMember = StaffMember::where('user_id', $request->user()->id)->first();

        if (!$staffMember) {
            return response()->json([
                'message' => 'Staff member not found',
            ], 404);
        }

        $today = now()->toDateString();
        $attendance = Attendance::where('staff_member_id', $staffMember->id)
            ->whereDate('date', $today)
            ->first();

        if ($attendance && $attendance->clock_in) {
            return response()->json([
                'message' => 'Already clocked in today',
            ], 422);
        }

        $clockIn = now()->format('H:i:s');
        $companyStartTime = CompanySetting::getValue('company_start_time', '09:00:00');
        $lateMinutes = 0;

        if ($clockIn > $companyStartTime) {
            $lateMinutes = Carbon::parse($companyStartTime)->diffInMinutes(Carbon::parse($clockIn));
        }

        $attendance = Attendance::updateOrCreate(
            [
                'staff_member_id' => $staffMember->id,
                'date' => $today,
            ],
            [
                'clock_in' => $clockIn,
                'status' => $lateMinutes > 0 ? 'late' : 'present',
                'late_minutes' => $lateMinutes,
                'author_id' => $request->user()->id,
            ]
        );

        return response()->json([
            'message' => 'Clocked in successfully',
            'data' => $attendance,
        ]);
    }

    public function clockOut(Request $request): JsonResponse
    {
        $staffMember = StaffMember::where('user_id', $request->user()->id)->first();

        if (!$staffMember) {
            return response()->json([
                'message' => 'Staff member not found',
            ], 404);
        }

        $today = now()->toDateString();
        $attendance = Attendance::where('staff_member_id', $staffMember->id)
            ->whereDate('date', $today)
            ->first();

        if (!$attendance || !$attendance->clock_in) {
            return response()->json([
                'message' => 'You need to clock in first',
            ], 422);
        }

        if ($attendance->clock_out) {
            return response()->json([
                'message' => 'Already clocked out today',
            ], 422);
        }

        $clockOut = now()->format('H:i:s');
        $companyEndTime = CompanySetting::getValue('company_end_time', '18:00:00');
        $earlyLeavingMinutes = 0;
        $overtimeMinutes = 0;

        if ($clockOut < $companyEndTime) {
            $earlyLeavingMinutes = Carbon::parse($clockOut)->diffInMinutes(Carbon::parse($companyEndTime));
        } elseif ($clockOut > $companyEndTime) {
            $overtimeMinutes = Carbon::parse($companyEndTime)->diffInMinutes(Carbon::parse($clockOut));
        }

        $totalWorkMinutes = Carbon::parse($attendance->clock_in)->diffInMinutes(Carbon::parse($clockOut));

        $attendance->update([
            'clock_out' => $clockOut,
            'early_leaving_minutes' => $earlyLeavingMinutes,
            'overtime_minutes' => $overtimeMinutes,
            'total_work_minutes' => $totalWorkMinutes,
        ]);

        return response()->json([
            'message' => 'Clocked out successfully',
            'data' => $attendance->fresh(),
        ]);
    }

    public function bulkStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'attendances' => ['required', 'array'],
            'attendances.*.staff_member_id' => ['required', 'exists:staff_members,id'],
            'attendances.*.status' => ['required', 'in:present,absent,half_day,late'],
            'attendances.*.clock_in' => ['nullable', 'date_format:H:i:s'],
            'attendances.*.clock_out' => ['nullable', 'date_format:H:i:s'],
        ]);

        $results = [];
        foreach ($validated['attendances'] as $attendanceData) {
            $results[] = Attendance::updateOrCreate(
                [
                    'staff_member_id' => $attendanceData['staff_member_id'],
                    'date' => $validated['date'],
                ],
                [
                    ...$attendanceData,
                    'author_id' => $request->user()->id,
                ]
            );
        }

        return response()->json([
            'message' => count($results) . ' attendance records saved successfully',
            'data' => $results,
        ], 201);
    }

    public function monthlyReport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer'],
            'staff_member_id' => ['nullable', 'exists:staff_members,id'],
            'office_location_id' => ['nullable', 'exists:office_locations,id'],
            'division_id' => ['nullable', 'exists:divisions,id'],
        ]);

        $query = StaffMember::with(['officeLocation', 'division', 'jobTitle']);

        if (isset($validated['staff_member_id'])) {
            $query->where('id', $validated['staff_member_id']);
        }

        if (isset($validated['office_location_id'])) {
            $query->where('office_location_id', $validated['office_location_id']);
        }

        if (isset($validated['division_id'])) {
            $query->where('division_id', $validated['division_id']);
        }

        $staffMembers = $query->get();
        $report = [];

        foreach ($staffMembers as $staffMember) {
            $attendances = Attendance::where('staff_member_id', $staffMember->id)
                ->whereMonth('date', $validated['month'])
                ->whereYear('date', $validated['year'])
                ->get();

            $report[] = [
                'staff_member' => $staffMember,
                'present_days' => $attendances->where('status', 'present')->count(),
                'absent_days' => $attendances->where('status', 'absent')->count(),
                'half_days' => $attendances->where('status', 'half_day')->count(),
                'late_days' => $attendances->where('status', 'late')->count(),
                'total_late_minutes' => $attendances->sum('late_minutes'),
                'total_overtime_minutes' => $attendances->sum('overtime_minutes'),
                'total_work_minutes' => $attendances->sum('total_work_minutes'),
            ];
        }

        return response()->json([
            'data' => [
                'month' => $validated['month'],
                'year' => $validated['year'],
                'report' => $report,
            ],
        ]);
    }
}
