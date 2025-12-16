<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\JobTitle;
use App\Models\OfficeLocation;
use App\Models\StaffMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffMemberTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected OfficeLocation $officeLocation;
    protected Division $division;
    protected JobTitle $jobTitle;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->officeLocation = OfficeLocation::factory()->create(['author_id' => $this->user->id]);
        $this->division = Division::factory()->create([
            'office_location_id' => $this->officeLocation->id,
            'author_id' => $this->user->id,
        ]);
        $this->jobTitle = JobTitle::factory()->create([
            'division_id' => $this->division->id,
            'author_id' => $this->user->id,
        ]);
    }

    public function test_can_list_staff_members(): void
    {
        StaffMember::factory()->count(3)->create([
            'office_location_id' => $this->officeLocation->id,
            'division_id' => $this->division->id,
            'job_title_id' => $this->jobTitle->id,
            'author_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/staff-members');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'full_name', 'staff_code', 'employment_status'],
                ],
            ]);
    }

    public function test_can_create_staff_member(): void
    {
        $staffUser = User::factory()->create();

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/staff-members', [
                'user_id' => $staffUser->id,
                'full_name' => 'John Doe',
                'personal_email' => 'john.doe@example.com',
                'mobile_number' => '+1234567890',
                'birth_date' => '1990-01-15',
                'gender' => 'male',
                'home_address' => '123 Main St',
                'staff_code' => 'EMP001',
                'office_location_id' => $this->officeLocation->id,
                'division_id' => $this->division->id,
                'job_title_id' => $this->jobTitle->id,
                'hire_date' => '2024-01-01',
                'compensation_type' => 'monthly',
                'base_salary' => 50000,
                'employment_status' => 'active',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'full_name', 'staff_code'],
            ]);

        $this->assertDatabaseHas('staff_members', [
            'full_name' => 'John Doe',
            'staff_code' => 'EMP001',
        ]);
    }

    public function test_can_show_staff_member(): void
    {
        $staffMember = StaffMember::factory()->create([
            'office_location_id' => $this->officeLocation->id,
            'division_id' => $this->division->id,
            'job_title_id' => $this->jobTitle->id,
            'author_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/staff-members/{$staffMember->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'full_name', 'staff_code', 'office_location', 'division', 'job_title'],
            ]);
    }

    public function test_can_update_staff_member(): void
    {
        $staffMember = StaffMember::factory()->create([
            'office_location_id' => $this->officeLocation->id,
            'division_id' => $this->division->id,
            'job_title_id' => $this->jobTitle->id,
            'author_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/staff-members/{$staffMember->id}", [
                'full_name' => 'Jane Doe Updated',
                'base_salary' => 60000,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('staff_members', [
            'id' => $staffMember->id,
            'full_name' => 'Jane Doe Updated',
            'base_salary' => 60000,
        ]);
    }

    public function test_can_delete_staff_member(): void
    {
        $staffMember = StaffMember::factory()->create([
            'office_location_id' => $this->officeLocation->id,
            'division_id' => $this->division->id,
            'job_title_id' => $this->jobTitle->id,
            'author_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/staff-members/{$staffMember->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('staff_members', [
            'id' => $staffMember->id,
        ]);
    }

    public function test_can_filter_staff_members_by_office_location(): void
    {
        $otherLocation = OfficeLocation::factory()->create(['author_id' => $this->user->id]);

        StaffMember::factory()->count(2)->create([
            'office_location_id' => $this->officeLocation->id,
            'division_id' => $this->division->id,
            'job_title_id' => $this->jobTitle->id,
            'author_id' => $this->user->id,
        ]);

        StaffMember::factory()->create([
            'office_location_id' => $otherLocation->id,
            'division_id' => $this->division->id,
            'job_title_id' => $this->jobTitle->id,
            'author_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/staff-members?office_location_id={$this->officeLocation->id}");

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }
}
