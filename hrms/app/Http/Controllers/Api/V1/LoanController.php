<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\StaffMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Loan::with(['staffMember', 'loanOption', 'author']);

        if ($request->has('staff_member_id')) {
            $query->where('staff_member_id', $request->staff_member_id);
        }

        if ($request->has('loan_option_id')) {
            $query->where('loan_option_id', $request->loan_option_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $loans = $request->has('per_page')
            ? $query->paginate($request->per_page)
            : $query->get();

        return response()->json([
            'data' => $loans,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'staff_member_id' => ['required', 'exists:staff_members,id'],
            'loan_option_id' => ['required', 'exists:loan_options,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:fixed,percentage'],
            'amount' => ['required', 'numeric', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string'],
            'installments' => ['nullable', 'integer', 'min:1'],
        ]);

        $validated['status'] = 'active';
        $validated['author_id'] = $request->user()->id;

        $loan = Loan::create($validated);

        return response()->json([
            'message' => 'Loan created successfully',
            'data' => $loan->load(['staffMember', 'loanOption']),
        ], 201);
    }

    public function show(Loan $loan): JsonResponse
    {
        return response()->json([
            'data' => $loan->load(['staffMember', 'loanOption', 'author']),
        ]);
    }

    public function update(Request $request, Loan $loan): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'in:fixed,percentage'],
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date'],
            'reason' => ['nullable', 'string'],
            'installments' => ['nullable', 'integer', 'min:1'],
            'status' => ['sometimes', 'in:active,paid,cancelled'],
        ]);

        $loan->update($validated);

        return response()->json([
            'message' => 'Loan updated successfully',
            'data' => $loan->fresh()->load(['staffMember', 'loanOption']),
        ]);
    }

    public function destroy(Loan $loan): JsonResponse
    {
        $loan->delete();

        return response()->json([
            'message' => 'Loan deleted successfully',
        ]);
    }

    public function byStaffMember(StaffMember $staffMember): JsonResponse
    {
        $loans = $staffMember->loans()->with('loanOption')->get();

        return response()->json([
            'data' => $loans,
        ]);
    }

    public function recordPayment(Request $request, Loan $loan): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $newRemaining = max(0, $loan->remaining_amount - $validated['amount']);
        $loan->update([
            'remaining_amount' => $newRemaining,
            'status' => $newRemaining <= 0 ? 'paid' : 'active',
        ]);

        return response()->json([
            'message' => 'Payment recorded successfully',
            'data' => $loan->fresh(),
        ]);
    }
}
