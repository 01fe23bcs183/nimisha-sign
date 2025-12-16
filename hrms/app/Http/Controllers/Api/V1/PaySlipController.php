<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PaySlip;
use App\Models\StaffMember;
use App\Models\TaxBracket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaySlipController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PaySlip::with(['staffMember', 'author']);

        if ($request->has('staff_member_id')) {
            $query->where('staff_member_id', $request->staff_member_id);
        }

        if ($request->has('salary_month')) {
            $query->where('salary_month', $request->salary_month);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $paySlips = $request->has('per_page')
            ? $query->orderBy('salary_month', 'desc')->paginate($request->per_page)
            : $query->orderBy('salary_month', 'desc')->get();

        return response()->json([
            'data' => $paySlips,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'staff_member_id' => ['required', 'exists:staff_members,id'],
            'salary_month' => ['required', 'date_format:Y-m'],
        ]);

        $staffMember = StaffMember::with([
            'allowances.allowanceOption',
            'commissions',
            'loans',
            'saturationDeductions.deductionOption',
            'otherPayments',
            'overtimes',
            'companyContributions',
        ])->findOrFail($validated['staff_member_id']);

        $existing = PaySlip::where('staff_member_id', $staffMember->id)
            ->where('salary_month', $validated['salary_month'])
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Payslip already exists for this month',
                'data' => $existing,
            ], 422);
        }

        $basicSalary = $staffMember->base_salary ?? 0;

        $allowances = $staffMember->allowances->map(function ($allowance) use ($basicSalary) {
            $amount = $allowance->type === 'percentage'
                ? ($basicSalary * $allowance->amount / 100)
                : $allowance->amount;
            return [
                'id' => $allowance->id,
                'title' => $allowance->title,
                'type' => $allowance->type,
                'original_amount' => $allowance->amount,
                'calculated_amount' => $amount,
            ];
        })->toArray();

        $commissions = $staffMember->commissions
            ->filter(function ($commission) use ($validated) {
                $month = $validated['salary_month'] . '-01';
                return $commission->start_date <= $month && $commission->end_date >= $month;
            })
            ->map(function ($commission) use ($basicSalary) {
                $amount = $commission->type === 'percentage'
                    ? ($basicSalary * $commission->amount / 100)
                    : $commission->amount;
                return [
                    'id' => $commission->id,
                    'title' => $commission->title,
                    'type' => $commission->type,
                    'original_amount' => $commission->amount,
                    'calculated_amount' => $amount,
                ];
            })->toArray();

        $loans = $staffMember->loans
            ->filter(function ($loan) use ($validated) {
                $month = $validated['salary_month'] . '-01';
                return $loan->status === 'active' && $loan->start_date <= $month && $loan->end_date >= $month;
            })
            ->map(function ($loan) {
                return [
                    'id' => $loan->id,
                    'title' => $loan->title,
                    'monthly_deduction' => $loan->monthly_deduction,
                ];
            })->toArray();

        $deductions = $staffMember->saturationDeductions->map(function ($deduction) use ($basicSalary) {
            $amount = $deduction->type === 'percentage'
                ? ($basicSalary * $deduction->amount / 100)
                : $deduction->amount;
            return [
                'id' => $deduction->id,
                'title' => $deduction->title,
                'type' => $deduction->type,
                'original_amount' => $deduction->amount,
                'calculated_amount' => $amount,
            ];
        })->toArray();

        $otherPayments = $staffMember->otherPayments
            ->filter(function ($payment) use ($validated) {
                if (!$payment->payment_date) return true;
                return str_starts_with($payment->payment_date->format('Y-m'), $validated['salary_month']);
            })
            ->map(function ($payment) use ($basicSalary) {
                $amount = $payment->type === 'percentage'
                    ? ($basicSalary * $payment->amount / 100)
                    : $payment->amount;
                return [
                    'id' => $payment->id,
                    'title' => $payment->title,
                    'type' => $payment->type,
                    'original_amount' => $payment->amount,
                    'calculated_amount' => $amount,
                ];
            })->toArray();

        $overtimes = $staffMember->overtimes
            ->filter(function ($overtime) use ($validated) {
                $month = $validated['salary_month'] . '-01';
                return $overtime->start_date <= $month && $overtime->end_date >= $month;
            })
            ->map(function ($overtime) {
                return [
                    'id' => $overtime->id,
                    'title' => $overtime->title,
                    'total_amount' => $overtime->total_amount,
                ];
            })->toArray();

        $companyContributions = $staffMember->companyContributions->map(function ($contribution) use ($basicSalary) {
            $amount = $contribution->type === 'percentage'
                ? ($basicSalary * $contribution->amount / 100)
                : $contribution->amount;
            return [
                'id' => $contribution->id,
                'title' => $contribution->title,
                'type' => $contribution->type,
                'original_amount' => $contribution->amount,
                'calculated_amount' => $amount,
            ];
        })->toArray();

        $totalAllowance = array_sum(array_column($allowances, 'calculated_amount'));
        $totalCommission = array_sum(array_column($commissions, 'calculated_amount'));
        $totalLoan = array_sum(array_column($loans, 'monthly_deduction'));
        $totalDeduction = array_sum(array_column($deductions, 'calculated_amount'));
        $totalOtherPayment = array_sum(array_column($otherPayments, 'calculated_amount'));
        $totalOvertime = array_sum(array_column($overtimes, 'total_amount'));
        $totalCompanyContribution = array_sum(array_column($companyContributions, 'calculated_amount'));

        $grossSalary = $basicSalary + $totalAllowance + $totalCommission + $totalOtherPayment + $totalOvertime;
        $taxAmount = TaxBracket::calculateTax($grossSalary);
        $netPayable = $grossSalary - $totalLoan - $totalDeduction - $taxAmount;

        $taxBracket = TaxBracket::where('from_amount', '<=', $grossSalary)
            ->where('to_amount', '>=', $grossSalary)
            ->first();

        $paySlip = PaySlip::create([
            'staff_member_id' => $staffMember->id,
            'salary_month' => $validated['salary_month'],
            'basic_salary' => $basicSalary,
            'allowance' => $allowances,
            'commission' => $commissions,
            'loan' => $loans,
            'saturation_deduction' => $deductions,
            'other_payment' => $otherPayments,
            'overtime' => $overtimes,
            'company_contribution' => $companyContributions,
            'tax_bracket' => $taxBracket ? $taxBracket->toArray() : null,
            'total_allowance' => $totalAllowance,
            'total_commission' => $totalCommission,
            'total_loan' => $totalLoan,
            'total_deduction' => $totalDeduction,
            'total_other_payment' => $totalOtherPayment,
            'total_overtime' => $totalOvertime,
            'total_company_contribution' => $totalCompanyContribution,
            'tax_amount' => $taxAmount,
            'gross_salary' => $grossSalary,
            'net_payable' => $netPayable,
            'status' => 'generated',
            'author_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Payslip generated successfully',
            'data' => $paySlip->load('staffMember'),
        ], 201);
    }

    public function show(PaySlip $paySlip): JsonResponse
    {
        return response()->json([
            'data' => $paySlip->load(['staffMember', 'author']),
        ]);
    }

    public function update(Request $request, PaySlip $paySlip): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['sometimes', 'in:generated,paid,cancelled'],
            'payment_date' => ['nullable', 'date'],
        ]);

        $paySlip->update($validated);

        return response()->json([
            'message' => 'Payslip updated successfully',
            'data' => $paySlip->fresh()->load('staffMember'),
        ]);
    }

    public function destroy(PaySlip $paySlip): JsonResponse
    {
        $paySlip->delete();

        return response()->json([
            'message' => 'Payslip deleted successfully',
        ]);
    }

    public function bulkGenerate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'salary_month' => ['required', 'date_format:Y-m'],
            'office_location_id' => ['nullable', 'exists:office_locations,id'],
            'division_id' => ['nullable', 'exists:divisions,id'],
        ]);

        $query = StaffMember::where('employment_status', 'active');

        if (isset($validated['office_location_id'])) {
            $query->where('office_location_id', $validated['office_location_id']);
        }

        if (isset($validated['division_id'])) {
            $query->where('division_id', $validated['division_id']);
        }

        $staffMembers = $query->get();
        $generated = 0;
        $skipped = 0;

        foreach ($staffMembers as $staffMember) {
            $existing = PaySlip::where('staff_member_id', $staffMember->id)
                ->where('salary_month', $validated['salary_month'])
                ->exists();

            if ($existing) {
                $skipped++;
                continue;
            }

            $request->merge(['staff_member_id' => $staffMember->id]);
            $this->store($request);
            $generated++;
        }

        return response()->json([
            'message' => "Bulk generation complete: {$generated} generated, {$skipped} skipped",
            'data' => [
                'generated' => $generated,
                'skipped' => $skipped,
            ],
        ]);
    }

    public function markAsPaid(PaySlip $paySlip): JsonResponse
    {
        $paySlip->update([
            'status' => 'paid',
            'payment_date' => now()->toDateString(),
        ]);

        return response()->json([
            'message' => 'Payslip marked as paid',
            'data' => $paySlip->fresh(),
        ]);
    }

    public function byStaffMember(StaffMember $staffMember): JsonResponse
    {
        $paySlips = $staffMember->paySlips()->orderBy('salary_month', 'desc')->get();

        return response()->json([
            'data' => $paySlips,
        ]);
    }
}
