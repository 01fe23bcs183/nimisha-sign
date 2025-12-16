<?php

namespace Database\Seeders;

use App\Models\AllowanceOption;
use App\Models\DeductionOption;
use App\Models\LoanOption;
use App\Models\PayslipType;
use App\Models\User;
use Illuminate\Database\Seeder;

class PayrollOptionSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@hrms.com')->first();
        $authorId = $admin?->id ?? 1;

        $payslipTypes = [
            ['name' => 'Monthly Salary', 'description' => 'Regular monthly salary payment'],
            ['name' => 'Weekly Wage', 'description' => 'Weekly wage payment'],
            ['name' => 'Hourly Rate', 'description' => 'Hourly rate payment'],
            ['name' => 'Contract Payment', 'description' => 'Contract-based payment'],
        ];

        foreach ($payslipTypes as $type) {
            PayslipType::firstOrCreate(
                ['name' => $type['name']],
                [
                    'description' => $type['description'],
                    'is_active' => true,
                    'author_id' => $authorId,
                ]
            );
        }

        $allowanceOptions = [
            ['name' => 'House Rent Allowance (HRA)', 'description' => 'Allowance for housing expenses'],
            ['name' => 'Transport Allowance', 'description' => 'Allowance for transportation'],
            ['name' => 'Medical Allowance', 'description' => 'Allowance for medical expenses'],
            ['name' => 'Meal Allowance', 'description' => 'Allowance for meals'],
            ['name' => 'Communication Allowance', 'description' => 'Allowance for phone and internet'],
            ['name' => 'Education Allowance', 'description' => 'Allowance for education expenses'],
            ['name' => 'Special Allowance', 'description' => 'Special additional allowance'],
        ];

        foreach ($allowanceOptions as $option) {
            AllowanceOption::firstOrCreate(
                ['name' => $option['name']],
                [
                    'description' => $option['description'],
                    'is_active' => true,
                    'author_id' => $authorId,
                ]
            );
        }

        $loanOptions = [
            ['name' => 'Personal Loan', 'description' => 'Personal loan from company'],
            ['name' => 'Salary Advance', 'description' => 'Advance on salary'],
            ['name' => 'Emergency Loan', 'description' => 'Emergency financial assistance'],
            ['name' => 'Housing Loan', 'description' => 'Loan for housing purposes'],
            ['name' => 'Vehicle Loan', 'description' => 'Loan for vehicle purchase'],
        ];

        foreach ($loanOptions as $option) {
            LoanOption::firstOrCreate(
                ['name' => $option['name']],
                [
                    'description' => $option['description'],
                    'is_active' => true,
                    'author_id' => $authorId,
                ]
            );
        }

        $deductionOptions = [
            ['name' => 'Income Tax', 'description' => 'Tax deduction at source'],
            ['name' => 'Provident Fund (PF)', 'description' => 'Employee provident fund contribution'],
            ['name' => 'Professional Tax', 'description' => 'Professional tax deduction'],
            ['name' => 'Health Insurance', 'description' => 'Health insurance premium'],
            ['name' => 'Life Insurance', 'description' => 'Life insurance premium'],
            ['name' => 'Union Dues', 'description' => 'Labor union membership dues'],
        ];

        foreach ($deductionOptions as $option) {
            DeductionOption::firstOrCreate(
                ['name' => $option['name']],
                [
                    'description' => $option['description'],
                    'is_active' => true,
                    'author_id' => $authorId,
                ]
            );
        }
    }
}
