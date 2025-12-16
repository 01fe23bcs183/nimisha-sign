<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\StaffMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        $clockIn = fake()->time('H:i:s', '10:00:00');
        $clockOut = fake()->time('H:i:s', '18:00:00');

        return [
            'staff_member_id' => StaffMember::factory(),
            'date' => fake()->dateTimeBetween('-30 days', 'now'),
            'status' => fake()->randomElement(['present', 'absent', 'half_day', 'late']),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'late_minutes' => fake()->numberBetween(0, 60),
            'early_leaving_minutes' => fake()->numberBetween(0, 30),
            'overtime_minutes' => fake()->numberBetween(0, 120),
            'total_rest_minutes' => fake()->numberBetween(30, 60),
            'total_work_minutes' => fake()->numberBetween(400, 540),
            'notes' => fake()->optional()->sentence(),
            'author_id' => User::factory(),
        ];
    }

    public function present(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'present',
            'late_minutes' => 0,
        ]);
    }

    public function absent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'absent',
            'clock_in' => null,
            'clock_out' => null,
            'total_work_minutes' => 0,
        ]);
    }

    public function late(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'late',
            'late_minutes' => fake()->numberBetween(15, 60),
        ]);
    }
}
