<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\PaySlip;
use App\Models\StaffMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function monthlyAttendance(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer'],
            'office_location_id' => ['nullable', 'exists:office_locations,id'],
            'division_id' => ['nullable', 'exists:divisions,id'],
            'staff_member_id' => ['nullable', 'exists:staff_members,id'],
        ]);

        $query = StaffMember::with(['officeLocation', 'division', 'jobTitle']);

        if (isset($validated['office_location_id'])) {
            $query->where('office_location_id', $validated['office_location_id']);
        }

        if (isset($validated['division_id'])) {
            $query->where('division_id', $validated['division_id']);
        }

        if (isset($validated['staff_member_id'])) {
            $query->where('id', $validated['staff_member_id']);
        }

        $staffMembers = $query->get();
        $report = [];

        foreach ($staffMembers as $staffMember) {
            $attendances = Attendance::where('staff_member_id', $staffMember->id)
                ->whereMonth('date', $validated['month'])
                ->whereYear('date', $validated['year'])
                ->get();

            $report[] = [
                'staff_member' => [
                    'id' => $staffMember->id,
                    'full_name' => $staffMember->full_name,
                    'staff_code' => $staffMember->staff_code,
                    'office_location' => $staffMember->officeLocation?->title,
                    'division' => $staffMember->division?->title,
                    'job_title' => $staffMember->jobTitle?->title,
                ],
                'summary' => [
                    'present_days' => $attendances->where('status', 'present')->count(),
                    'absent_days' => $attendances->where('status', 'absent')->count(),
                    'half_days' => $attendances->where('status', 'half_day')->count(),
                    'late_days' => $attendances->where('status', 'late')->count(),
                    'total_late_minutes' => $attendances->sum('late_minutes'),
                    'total_early_leaving_minutes' => $attendances->sum('early_leaving_minutes'),
                    'total_overtime_minutes' => $attendances->sum('overtime_minutes'),
                    'total_work_minutes' => $attendances->sum('total_work_minutes'),
                ],
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

    public function leaveReport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['required', 'integer'],
            'leave_type_id' => ['nullable', 'exists:leave_types,id'],
            'office_location_id' => ['nullable', 'exists:office_locations,id'],
            'division_id' => ['nullable', 'exists:divisions,id'],
            'staff_member_id' => ['nullable', 'exists:staff_members,id'],
        ]);

        $query = StaffMember::with(['officeLocation', 'division', 'jobTitle']);

        if (isset($validated['office_location_id'])) {
            $query->where('office_location_id', $validated['office_location_id']);
        }

        if (isset($validated['division_id'])) {
            $query->where('division_id', $validated['division_id']);
        }

        if (isset($validated['staff_member_id'])) {
            $query->where('id', $validated['staff_member_id']);
        }

        $staffMembers = $query->get();
        $report = [];

        foreach ($staffMembers as $staffMember) {
            $leaveQuery = Leave::where('staff_member_id', $staffMember->id)
                ->whereYear('start_date', $validated['year']);

            if (isset($validated['month'])) {
                $leaveQuery->whereMonth('start_date', $validated['month']);
            }

            if (isset($validated['leave_type_id'])) {
                $leaveQuery->where('leave_type_id', $validated['leave_type_id']);
            }

            $leaves = $leaveQuery->with('leaveType')->get();

            $report[] = [
                'staff_member' => [
                    'id' => $staffMember->id,
                    'full_name' => $staffMember->full_name,
                    'staff_code' => $staffMember->staff_code,
                    'office_location' => $staffMember->officeLocation?->title,
                    'division' => $staffMember->division?->title,
                ],
                'summary' => [
                    'total_leaves' => $leaves->count(),
                    'approved_leaves' => $leaves->where('status', 'approved')->count(),
                    'pending_leaves' => $leaves->where('status', 'pending')->count(),
                    'rejected_leaves' => $leaves->where('status', 'rejected')->count(),
                    'total_days_taken' => $leaves->where('status', 'approved')->sum('total_leave_days'),
                ],
                'leaves' => $leaves,
            ];
        }

        return response()->json([
            'data' => [
                'year' => $validated['year'],
                'month' => $validated['month'] ?? null,
                'report' => $report,
            ],
        ]);
    }

    public function payrollReport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'salary_month' => ['required', 'date_format:Y-m'],
            'office_location_id' => ['nullable', 'exists:office_locations,id'],
            'division_id' => ['nullable', 'exists:divisions,id'],
            'status' => ['nullable', 'in:generated,paid,cancelled'],
        ]);

        $query = PaySlip::with(['staffMember.officeLocation', 'staffMember.division', 'staffMember.jobTitle'])
            ->where('salary_month', $validated['salary_month']);

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (isset($validated['office_location_id'])) {
            $query->whereHas('staffMember', function ($q) use ($validated) {
                $q->where('office_location_id', $validated['office_location_id']);
            });
        }

        if (isset($validated['division_id'])) {
            $query->whereHas('staffMember', function ($q) use ($validated) {
                $q->where('division_id', $validated['division_id']);
            });
        }

        $paySlips = $query->get();

        $summary = [
            'total_payslips' => $paySlips->count(),
            'total_basic_salary' => $paySlips->sum('basic_salary'),
            'total_allowances' => $paySlips->sum('total_allowance'),
            'total_commissions' => $paySlips->sum('total_commission'),
            'total_overtime' => $paySlips->sum('total_overtime'),
            'total_other_payments' => $paySlips->sum('total_other_payment'),
            'total_gross_salary' => $paySlips->sum('gross_salary'),
            'total_loans' => $paySlips->sum('total_loan'),
            'total_deductions' => $paySlips->sum('total_deduction'),
            'total_tax' => $paySlips->sum('tax_amount'),
            'total_net_payable' => $paySlips->sum('net_payable'),
            'total_company_contributions' => $paySlips->sum('total_company_contribution'),
        ];

        return response()->json([
            'data' => [
                'salary_month' => $validated['salary_month'],
                'summary' => $summary,
                'payslips' => $paySlips,
            ],
        ]);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $totalStaff = StaffMember::count();
        $activeStaff = StaffMember::where('employment_status', 'active')->count();
        $onProbation = StaffMember::where('employment_status', 'probation')->count();

        $today = now()->toDateString();
        $thisMonth = now()->month;
        $thisYear = now()->year;

        $todayAttendance = Attendance::whereDate('date', $today)->get();
        $presentToday = $todayAttendance->where('status', 'present')->count();
        $absentToday = $todayAttendance->where('status', 'absent')->count();
        $lateToday = $todayAttendance->where('status', 'late')->count();

        $pendingLeaves = Leave::where('status', 'pending')->count();
        $approvedLeavesToday = Leave::where('status', 'approved')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->count();

        $recentAnnouncements = \App\Models\Announcement::where('is_active', true)
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $upcomingEvents = \App\Models\Event::where('is_active', true)
            ->where('start_date', '>=', $today)
            ->orderBy('start_date', 'asc')
            ->limit(5)
            ->get();

        $upcomingHolidays = \App\Models\Holiday::where('is_active', true)
            ->where('date', '>=', $today)
            ->orderBy('date', 'asc')
            ->limit(5)
            ->get();

        return response()->json([
            'data' => [
                'staff' => [
                    'total' => $totalStaff,
                    'active' => $activeStaff,
                    'on_probation' => $onProbation,
                ],
                'attendance_today' => [
                    'present' => $presentToday,
                    'absent' => $absentToday,
                    'late' => $lateToday,
                ],
                'leaves' => [
                    'pending_requests' => $pendingLeaves,
                    'on_leave_today' => $approvedLeavesToday,
                ],
                'recent_announcements' => $recentAnnouncements,
                'upcoming_events' => $upcomingEvents,
                'upcoming_holidays' => $upcomingHolidays,
            ],
        ]);
    }
}
