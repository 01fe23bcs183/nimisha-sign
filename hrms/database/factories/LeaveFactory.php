<?php

namespace Database\Factories;

use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\StaffMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveFactory extends Factory
{
    protected $model = Leave::class;

    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('now', '+14 days');
        $daysToAdd = fake()->numberBetween(1, 7);
        $endDate = (clone $startDate)->modify("+{$daysToAdd} days");
        $totalDays = $daysToAdd + 1;

        return [
            'staff_member_id' => StaffMember::factory(),
            'user_id' => User::factory(),
            'leave_type_id' => LeaveType::factory(),
            'applied_on' => fake()->dateTimeBetween('-30 days', 'now'),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_leave_days' => $totalDays,
            'leave_reason' => fake()->sentence(),
            'remark' => fake()->optional()->sentence(),
            'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
            'author_id' => User::factory(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_by' => User::factory(),
            'approved_date' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
        ]);
    }
}
