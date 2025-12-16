<?php

namespace Database\Factories;

use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveTypeFactory extends Factory
{
    protected $model = LeaveType::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Annual', 'Sick', 'Casual', 'Maternity', 'Paternity']) . ' Leave ' . fake()->unique()->numerify('##'),
            'days' => fake()->numberBetween(5, 30),
            'description' => fake()->sentence(),
            'is_active' => true,
            'author_id' => User::factory(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
