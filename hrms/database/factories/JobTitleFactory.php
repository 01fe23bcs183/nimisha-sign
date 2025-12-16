<?php

namespace Database\Factories;

use App\Models\Division;
use App\Models\JobTitle;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobTitleFactory extends Factory
{
    protected $model = JobTitle::class;

    public function definition(): array
    {
        return [
            'title' => fake()->jobTitle(),
            'division_id' => Division::factory(),
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
