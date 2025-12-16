<?php

namespace Tests\Unit;

use App\Models\TaxBracket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();

        TaxBracket::create([
            'name' => 'No Tax',
            'from_amount' => 0,
            'to_amount' => 250000,
            'fixed_amount' => 0,
            'percentage' => 0,
            'author_id' => $user->id,
        ]);

        TaxBracket::create([
            'name' => '5% Bracket',
            'from_amount' => 250001,
            'to_amount' => 500000,
            'fixed_amount' => 0,
            'percentage' => 5,
            'author_id' => $user->id,
        ]);

        TaxBracket::create([
            'name' => '10% Bracket',
            'from_amount' => 500001,
            'to_amount' => 750000,
            'fixed_amount' => 12500,
            'percentage' => 10,
            'author_id' => $user->id,
        ]);

        TaxBracket::create([
            'name' => '15% Bracket',
            'from_amount' => 750001,
            'to_amount' => 1000000,
            'fixed_amount' => 37500,
            'percentage' => 15,
            'author_id' => $user->id,
        ]);
    }

    public function test_no_tax_for_income_below_threshold(): void
    {
        $tax = TaxBracket::calculateTax(200000);
        $this->assertEquals(0, $tax);
    }

    public function test_five_percent_bracket(): void
    {
        $tax = TaxBracket::calculateTax(400000);
        $expectedTax = 0 + ((400000 - 250001) * 5 / 100);
        $this->assertEquals($expectedTax, $tax);
    }

    public function test_ten_percent_bracket(): void
    {
        $tax = TaxBracket::calculateTax(600000);
        $expectedTax = 12500 + ((600000 - 500001) * 10 / 100);
        $this->assertEquals($expectedTax, $tax);
    }

    public function test_fifteen_percent_bracket(): void
    {
        $tax = TaxBracket::calculateTax(900000);
        $expectedTax = 37500 + ((900000 - 750001) * 15 / 100);
        $this->assertEquals($expectedTax, $tax);
    }

    public function test_zero_income_returns_zero_tax(): void
    {
        $tax = TaxBracket::calculateTax(0);
        $this->assertEquals(0, $tax);
    }

    public function test_exact_bracket_boundary(): void
    {
        $tax = TaxBracket::calculateTax(250000);
        $this->assertEquals(0, $tax);
    }
}
