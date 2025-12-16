<?php

namespace Database\Factories;

use App\Models\PaySlip;
use App\Models\StaffMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaySlipFactory extends Factory
{
    protected $model = PaySlip::class;

    public function definition(): array
    {
        $basicSalary = fake()->randomFloat(2, 30000, 100000);
        $totalAllowance = fake()->randomFloat(2, 5000, 20000);
        $totalCommission = fake()->randomFloat(2, 0, 10000);
        $totalOvertime = fake()->randomFloat(2, 0, 5000);
        $totalOtherPayment = fake()->randomFloat(2, 0, 3000);
        $grossSalary = $basicSalary + $totalAllowance + $totalCommission + $totalOvertime + $totalOtherPayment;
        $totalLoan = fake()->randomFloat(2, 0, 5000);
        $totalDeduction = fake()->randomFloat(2, 0, 3000);
        $taxAmount = $grossSalary * 0.1;
        $netPayable = $grossSalary - $totalLoan - $totalDeduction - $taxAmount;

        return [
            'staff_member_id' => StaffMember::factory(),
            'salary_month' => fake()->dateTimeBetween('-12 months', 'now')->format('Y-m'),
            'basic_salary' => $basicSalary,
            'allowance' => [],
            'commission' => [],
            'loan' => [],
            'saturation_deduction' => [],
            'other_payment' => [],
            'overtime' => [],
            'company_contribution' => [],
            'tax_bracket' => null,
            'total_allowance' => $totalAllowance,
            'total_commission' => $totalCommission,
            'total_loan' => $totalLoan,
            'total_deduction' => $totalDeduction,
            'total_other_payment' => $totalOtherPayment,
            'total_overtime' => $totalOvertime,
            'total_company_contribution' => fake()->randomFloat(2, 0, 5000),
            'tax_amount' => $taxAmount,
            'gross_salary' => $grossSalary,
            'net_payable' => $netPayable,
            'status' => fake()->randomElement(['generated', 'paid', 'cancelled']),
            'payment_date' => fake()->optional()->date(),
            'author_id' => User::factory(),
        ];
    }

    public function generated(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'generated',
            'payment_date' => null,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'payment_date' => now()->toDateString(),
        ]);
    }
}
