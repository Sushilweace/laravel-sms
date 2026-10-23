<?php

namespace Tests\Unit;

use App\Support\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_money_addition_and_subtraction(): void
    {
        $this->assertEquals('150.75', Money::add('100.50', '50.25'));
        $this->assertEquals('50.25', Money::sub('100.50', '50.25'));
        $this->assertEquals('0.00', Money::sub('50.00', '50.00'));
    }

    public function test_money_percentage_rounding_half_up(): void
    {
        // 10% of 150.00 = 15.00
        $this->assertEquals('15.00', Money::percent('150.00', '10'));

        // 33.333% of 100 = 33.33
        $this->assertEquals('33.33', Money::percent('100.00', '33.33'));
    }

    public function test_money_comparisons(): void
    {
        $this->assertTrue(Money::gt('100.01', '100.00'));
        $this->assertFalse(Money::gt('100.00', '100.00'));

        $this->assertTrue(Money::gte('100.00', '100.00'));
        $this->assertTrue(Money::lt('99.99', '100.00'));
        $this->assertTrue(Money::lte('100.00', '100.00'));
        $this->assertTrue(Money::eq('50.50', '50.50'));
    }

    public function test_money_formatting(): void
    {
        $this->assertEquals('₹ 1,500.50', Money::format('1500.50'));
    }
}
