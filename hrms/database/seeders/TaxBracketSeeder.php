<?php

namespace Database\Seeders;

use App\Models\TaxBracket;
use App\Models\TaxRebate;
use App\Models\TaxThreshold;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaxBracketSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@hrms.com')->first();
        $authorId = $admin?->id ?? 1;

        $taxBrackets = [
            [
                'name' => 'No Tax',
                'from_amount' => 0,
                'to_amount' => 250000,
                'fixed_amount' => 0,
                'percentage' => 0,
            ],
            [
                'name' => '5% Bracket',
                'from_amount' => 250001,
                'to_amount' => 500000,
                'fixed_amount' => 0,
                'percentage' => 5,
            ],
            [
                'name' => '10% Bracket',
                'from_amount' => 500001,
                'to_amount' => 750000,
                'fixed_amount' => 12500,
                'percentage' => 10,
            ],
            [
                'name' => '15% Bracket',
                'from_amount' => 750001,
                'to_amount' => 1000000,
                'fixed_amount' => 37500,
                'percentage' => 15,
            ],
            [
                'name' => '20% Bracket',
                'from_amount' => 1000001,
                'to_amount' => 1250000,
                'fixed_amount' => 75000,
                'percentage' => 20,
            ],
            [
                'name' => '25% Bracket',
                'from_amount' => 1250001,
                'to_amount' => 1500000,
                'fixed_amount' => 125000,
                'percentage' => 25,
            ],
            [
                'name' => '30% Bracket',
                'from_amount' => 1500001,
                'to_amount' => 999999999,
                'fixed_amount' => 187500,
                'percentage' => 30,
            ],
        ];

        foreach ($taxBrackets as $bracket) {
            TaxBracket::firstOrCreate(
                ['name' => $bracket['name']],
                [
                    'from_amount' => $bracket['from_amount'],
                    'to_amount' => $bracket['to_amount'],
                    'fixed_amount' => $bracket['fixed_amount'],
                    'percentage' => $bracket['percentage'],
                    'author_id' => $authorId,
                ]
            );
        }

        $taxRebates = [
            ['name' => 'Standard Rebate', 'amount' => 12500, 'description' => 'Standard tax rebate for income up to 5 lakhs'],
            ['name' => 'Senior Citizen Rebate', 'amount' => 25000, 'description' => 'Additional rebate for senior citizens'],
        ];

        foreach ($taxRebates as $rebate) {
            TaxRebate::firstOrCreate(
                ['name' => $rebate['name']],
                [
                    'amount' => $rebate['amount'],
                    'description' => $rebate['description'],
                    'author_id' => $authorId,
                ]
            );
        }

        $taxThresholds = [
            ['name' => 'Basic Exemption', 'threshold_amount' => 250000, 'description' => 'Basic exemption limit'],
            ['name' => 'Senior Citizen Exemption', 'threshold_amount' => 300000, 'description' => 'Exemption limit for senior citizens'],
            ['name' => 'Super Senior Exemption', 'threshold_amount' => 500000, 'description' => 'Exemption limit for super senior citizens'],
        ];

        foreach ($taxThresholds as $threshold) {
            TaxThreshold::firstOrCreate(
                ['name' => $threshold['name']],
                [
                    'threshold_amount' => $threshold['threshold_amount'],
                    'description' => $threshold['description'],
                    'author_id' => $authorId,
                ]
            );
        }
    }
}
