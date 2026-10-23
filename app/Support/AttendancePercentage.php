<?php

namespace App\Support;

class AttendancePercentage
{
    public static function calculate(int $present, int $late, int $absent, int $leave, bool $lateCountsPresent = true, bool $leaveExcused = true): float
    {
        $countedPresent = $present + ($lateCountsPresent ? $late : 0);
        $totalDays = $present + $late + $absent + ($leaveExcused ? 0 : $leave);

        if ($totalDays <= 0) {
            return 100.00;
        }

        $percentage = ($countedPresent / $totalDays) * 100;

        return round(min(100.00, max(0.00, $percentage)), 2);
    }
}
