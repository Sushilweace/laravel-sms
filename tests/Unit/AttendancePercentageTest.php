<?php

namespace Tests\Unit;

use App\Support\AttendancePercentage;
use PHPUnit\Framework\TestCase;

class AttendancePercentageTest extends TestCase
{
    public function test_perfect_attendance(): void
    {
        $pct = AttendancePercentage::calculate(20, 0, 0, 0);
        $this->assertEquals(100.00, $pct);
    }

    public function test_late_arrivals_count_as_present(): void
    {
        // 18 present, 2 late out of 20 = 100% counted present
        $pct = AttendancePercentage::calculate(18, 2, 0, 0, true);
        $this->assertEquals(100.00, $pct);
    }

    public function test_excused_leave_not_counted_in_denominator(): void
    {
        // 15 present, 5 absent, 5 excused leave -> 15 / (15 + 5) = 75%
        $pct = AttendancePercentage::calculate(15, 0, 5, 5, true, true);
        $this->assertEquals(75.00, $pct);
    }

    public function test_zero_working_days_avoids_division_by_zero(): void
    {
        $pct = AttendancePercentage::calculate(0, 0, 0, 0);
        $this->assertEquals(100.00, $pct);
    }
}
