<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\StaffMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected StaffMember $staffMember;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->staffMember = StaffMember::factory()->create([
            'user_id' => $this->user->id,
            'author_id' => $this->user->id,
        ]);
    }

    public function test_can_list_attendances(): void
    {
        Attendance::factory()->count(3)->create([
            'staff_member_id' => $this->staffMember->id,
            'author_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/attendances');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'staff_member_id', 'date', 'status'],
                ],
            ]);
    }

    public function test_can_mark_attendance(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/attendances', [
                'staff_member_id' => $this->staffMember->id,
                'date' => '2024-12-16',
                'status' => 'present',
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'staff_member_id', 'status'],
            ]);

        $this->assertDatabaseHas('attendances', [
            'staff_member_id' => $this->staffMember->id,
            'date' => '2024-12-16',
            'status' => 'present',
        ]);
    }

    public function test_can_clock_in(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/attendance/clock-in');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'clock_in', 'status'],
            ]);

        $this->assertDatabaseHas('attendances', [
            'staff_member_id' => $this->staffMember->id,
            'date' => now()->toDateString(),
        ]);
    }

    public function test_cannot_clock_in_twice(): void
    {
        Attendance::factory()->create([
            'staff_member_id' => $this->staffMember->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'author_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/attendance/clock-in');

        $response->assertStatus(422);
    }

    public function test_can_clock_out(): void
    {
        Attendance::factory()->create([
            'staff_member_id' => $this->staffMember->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => null,
            'author_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/attendance/clock-out');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'clock_in', 'clock_out'],
            ]);
    }

    public function test_can_bulk_mark_attendance(): void
    {
        $staffMember2 = StaffMember::factory()->create(['author_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/attendance/bulk', [
                'date' => '2024-12-16',
                'attendances' => [
                    [
                        'staff_member_id' => $this->staffMember->id,
                        'status' => 'present',
                        'clock_in' => '09:00:00',
                        'clock_out' => '18:00:00',
                    ],
                    [
                        'staff_member_id' => $staffMember2->id,
                        'status' => 'absent',
                    ],
                ],
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('attendances', [
            'staff_member_id' => $this->staffMember->id,
            'date' => '2024-12-16',
            'status' => 'present',
        ]);

        $this->assertDatabaseHas('attendances', [
            'staff_member_id' => $staffMember2->id,
            'date' => '2024-12-16',
            'status' => 'absent',
        ]);
    }

    public function test_can_get_monthly_attendance_report(): void
    {
        Attendance::factory()->count(5)->create([
            'staff_member_id' => $this->staffMember->id,
            'date' => now()->startOfMonth(),
            'author_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/attendance/monthly-report?month=' . now()->month . '&year=' . now()->year);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'month',
                    'year',
                    'report' => [
                        '*' => [
                            'staff_member',
                            'present_days',
                            'absent_days',
                            'late_days',
                        ],
                    ],
                ],
            ]);
    }
}
