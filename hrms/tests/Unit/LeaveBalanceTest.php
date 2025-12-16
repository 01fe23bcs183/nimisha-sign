<?php

namespace Tests\Unit;

use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\StaffMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveBalanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected StaffMember $staffMember;
    protected LeaveType $leaveType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->staffMember = StaffMember::factory()->create(['author_id' => $this->user->id]);
        $this->leaveType = LeaveType::factory()->create([
            'days' => 20,
            'author_id' => $this->user->id,
        ]);
    }

    public function test_leave_days_calculated_correctly(): void
    {
        $leave = Leave::factory()->create([
            'staff_member_id' => $this->staffMember->id,
            'leave_type_id' => $this->leaveType->id,
            'user_id' => $this->user->id,
            'author_id' => $this->user->id,
            'start_date' => '2024-12-16',
            'end_date' => '2024-12-20',
        ]);

        $this->assertEquals(5, $leave->total_leave_days);
    }

    public function test_single_day_leave(): void
    {
        $leave = Leave::factory()->create([
            'staff_member_id' => $this->staffMember->id,
            'leave_type_id' => $this->leaveType->id,
            'user_id' => $this->user->id,
            'author_id' => $this->user->id,
            'start_date' => '2024-12-16',
            'end_date' => '2024-12-16',
        ]);

        $this->assertEquals(1, $leave->total_leave_days);
    }

    public function test_leave_balance_decreases_after_approval(): void
    {
        $year = now()->year;

        Leave::factory()->approved()->create([
            'staff_member_id' => $this->staffMember->id,
            'leave_type_id' => $this->leaveType->id,
            'user_id' => $this->user->id,
            'author_id' => $this->user->id,
            'start_date' => "{$year}-06-01",
            'end_date' => "{$year}-06-05",
            'total_leave_days' => 5,
        ]);

        $takenDays = Leave::where('staff_member_id', $this->staffMember->id)
            ->where('leave_type_id', $this->leaveType->id)
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->sum('total_leave_days');

        $remainingDays = $this->leaveType->days - $takenDays;

        $this->assertEquals(5, $takenDays);
        $this->assertEquals(15, $remainingDays);
    }

    public function test_pending_leaves_not_counted_in_balance(): void
    {
        $year = now()->year;

        Leave::factory()->pending()->create([
            'staff_member_id' => $this->staffMember->id,
            'leave_type_id' => $this->leaveType->id,
            'user_id' => $this->user->id,
            'author_id' => $this->user->id,
            'start_date' => "{$year}-06-01",
            'end_date' => "{$year}-06-05",
            'total_leave_days' => 5,
        ]);

        $takenDays = Leave::where('staff_member_id', $this->staffMember->id)
            ->where('leave_type_id', $this->leaveType->id)
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->sum('total_leave_days');

        $this->assertEquals(0, $takenDays);
    }
}
