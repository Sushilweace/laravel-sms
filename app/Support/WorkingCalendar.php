<?php

namespace App\Support;

use App\Models\SchoolCalendarDay;
use App\Models\Setting;
use Carbon\Carbon;

class WorkingCalendar
{
    public static function isWorkingDay(Carbon|string $date): bool
    {
        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        $dateStr = $carbon->toDateString();

        // Check explicit calendar exceptions first
        $exception = SchoolCalendarDay::where('date', $dateStr)->first();
        if ($exception) {
            return $exception->type === 'working' || $exception->type === 'half_day';
        }

        // Check weekly off day (default Sunday = 0)
        $weeklyOff = (int) Setting::get('calendar.weekly_off', 0);
        if ($carbon->dayOfWeek === $weeklyOff) {
            return false;
        }

        return true;
    }

    public static function schoolToday(): Carbon
    {
        $tz = Setting::get('school.timezone', 'Asia/Kolkata');

        return Carbon::now($tz);
    }
}
