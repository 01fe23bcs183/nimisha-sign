<?php

namespace Database\Factories;

use App\Models\Division;
use App\Models\OfficeLocation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DivisionFactory extends Factory
{
    protected $model = Division::class;

    public function definition(): array
    {
        return [
            'title' => fake()->randomElement(['Engineering', 'Marketing', 'Sales', 'Finance', 'HR', 'Operations', 'IT', 'Legal']) . ' ' . fake()->word(),
            'office_location_id' => OfficeLocation::factory(),
            'notes' => fake()->optional()->sentence(),
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
