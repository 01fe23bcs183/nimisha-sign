<?php

namespace Database\Factories;

use App\Models\Division;
use App\Models\JobTitle;
use App\Models\OfficeLocation;
use App\Models\StaffMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StaffMemberFactory extends Factory
{
    protected $model = StaffMember::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'full_name' => fake()->name(),
            'personal_email' => fake()->unique()->safeEmail(),
            'mobile_number' => fake()->phoneNumber(),
            'birth_date' => fake()->dateTimeBetween('-60 years', '-18 years'),
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'home_address' => fake()->address(),
            'nationality' => fake()->country(),
            'passport_number' => fake()->optional()->regexify('[A-Z]{2}[0-9]{7}'),
            'country_code' => fake()->countryCode(),
            'region' => fake()->state(),
            'city_name' => fake()->city(),
            'postal_code' => fake()->postcode(),
            'staff_code' => 'EMP' . fake()->unique()->numerify('####'),
            'biometric_id' => fake()->optional()->numerify('BIO####'),
            'office_location_id' => OfficeLocation::factory(),
            'division_id' => Division::factory(),
            'job_title_id' => JobTitle::factory(),
            'hire_date' => fake()->dateTimeBetween('-10 years', 'now'),
            'bank_account_name' => fake()->name(),
            'bank_account_number' => fake()->bankAccountNumber(),
            'bank_name' => fake()->company() . ' Bank',
            'bank_branch' => fake()->city() . ' Branch',
            'compensation_type' => fake()->randomElement(['monthly', 'hourly', 'contract']),
            'base_salary' => fake()->randomFloat(2, 30000, 150000),
            'employment_status' => fake()->randomElement(['active', 'probation', 'inactive', 'terminated']),
            'author_id' => User::factory(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'employment_status' => 'active',
        ]);
    }

    public function probation(): static
    {
        return $this->state(fn (array $attributes) => [
            'employment_status' => 'probation',
        ]);
    }
}
