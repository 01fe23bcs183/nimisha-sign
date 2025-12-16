<?php

namespace Tests\Feature;

use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\StaffMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveTest extends TestCase
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
        $this->leaveType = LeaveType::factory()->create(['author_id' => $this->user->id]);
    }

    public function test_can_list_leaves(): void
    {
        Leave::factory()->count(3)->create([
            'staff_member_id' => $this->staffMember->id,
            'leave_type_id' => $this->leaveType->id,
            'user_id' => $this->user->id,
            'author_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/leaves');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'staff_member_id', 'leave_type_id', 'status'],
                ],
            ]);
    }

    public function test_can_apply_for_leave(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/leaves', [
                'staff_member_id' => $this->staffMember->id,
                'leave_type_id' => $this->leaveType->id,
                'start_date' => '2024-12-20',
                'end_date' => '2024-12-25',
                'leave_reason' => 'Family vacation',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'staff_member_id', 'status'],
            ]);

        $this->assertDatabaseHas('leaves', [
            'staff_member_id' => $this->staffMember->id,
            'status' => 'pending',
        ]);
    }

    public function test_can_approve_leave(): void
    {
        $leave = Leave::factory()->pending()->create([
            'staff_member_id' => $this->staffMember->id,
            'leave_type_id' => $this->leaveType->id,
            'user_id' => $this->user->id,
            'author_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/leaves/{$leave->id}/approve", [
                'remark' => 'Approved for vacation',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('leaves', [
            'id' => $leave->id,
            'status' => 'approved',
        ]);
    }

    public function test_can_reject_leave(): void
    {
        $leave = Leave::factory()->pending()->create([
            'staff_member_id' => $this->staffMember->id,
            'leave_type_id' => $this->leaveType->id,
            'user_id' => $this->user->id,
            'author_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/leaves/{$leave->id}/reject", [
                'remark' => 'Insufficient leave balance',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('leaves', [
            'id' => $leave->id,
            'status' => 'rejected',
        ]);
    }

    public function test_cannot_update_approved_leave(): void
    {
        $leave = Leave::factory()->approved()->create([
            'staff_member_id' => $this->staffMember->id,
            'leave_type_id' => $this->leaveType->id,
            'user_id' => $this->user->id,
            'author_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/leaves/{$leave->id}", [
                'leave_reason' => 'Updated reason',
            ]);

        $response->assertStatus(422);
    }

    public function test_can_get_leave_balance(): void
    {
        Leave::factory()->approved()->create([
            'staff_member_id' => $this->staffMember->id,
            'leave_type_id' => $this->leaveType->id,
            'user_id' => $this->user->id,
            'author_id' => $this->user->id,
            'total_leave_days' => 5,
            'start_date' => now()->startOfYear(),
            'end_date' => now()->startOfYear()->addDays(4),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/staff-members/{$this->staffMember->id}/leave-balance");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'staff_member',
                    'year',
                    'balance' => [
                        '*' => ['leave_type', 'total_days', 'taken_days', 'remaining_days'],
                    ],
                ],
            ]);
    }
}
