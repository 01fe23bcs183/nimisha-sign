<?php

namespace Database\Factories;

use App\Models\OfficeLocation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OfficeLocationFactory extends Factory
{
    protected $model = OfficeLocation::class;

    public function definition(): array
    {
        return [
            'title' => fake()->company() . ' Office',
            'address' => fake()->address(),
            'contact_phone' => fake()->phoneNumber(),
            'contact_email' => fake()->companyEmail(),
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
