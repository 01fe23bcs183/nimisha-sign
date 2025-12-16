<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\OtherPayment;
use App\Models\StaffMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OtherPaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = OtherPayment::with(['staffMember', 'author']);

        if ($request->has('staff_member_id')) {
            $query->where('staff_member_id', $request->staff_member_id);
        }

        if ($request->has('date_from')) {
            $query->where('payment_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('payment_date', '<=', $request->date_to);
        }

        $payments = $request->has('per_page')
            ? $query->orderBy('payment_date', 'desc')->paginate($request->per_page)
            : $query->orderBy('payment_date', 'desc')->get();

        return response()->json([
            'data' => $payments,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'staff_member_id' => ['required', 'exists:staff_members,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:fixed,percentage'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['author_id'] = $request->user()->id;

        $payment = OtherPayment::create($validated);

        return response()->json([
            'message' => 'Other payment created successfully',
            'data' => $payment->load('staffMember'),
        ], 201);
    }

    public function show(OtherPayment $otherPayment): JsonResponse
    {
        return response()->json([
            'data' => $otherPayment->load(['staffMember', 'author']),
        ]);
    }

    public function update(Request $request, OtherPayment $otherPayment): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'in:fixed,percentage'],
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'payment_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $otherPayment->update($validated);

        return response()->json([
            'message' => 'Other payment updated successfully',
            'data' => $otherPayment->fresh()->load('staffMember'),
        ]);
    }

    public function destroy(OtherPayment $otherPayment): JsonResponse
    {
        $otherPayment->delete();

        return response()->json([
            'message' => 'Other payment deleted successfully',
        ]);
    }

    public function byStaffMember(StaffMember $staffMember): JsonResponse
    {
        $payments = $staffMember->otherPayments()->get();

        return response()->json([
            'data' => $payments,
        ]);
    }
}
