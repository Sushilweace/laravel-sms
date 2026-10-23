<?php

namespace Tests\Feature;

use App\Actions\Attendance\MarkAttendance;
use App\Models\Enrollment;
use App\Models\Section;
use App\Models\User;
use App\Support\WorkingCalendar;
use InvalidArgumentException;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    public function test_cannot_mark_attendance_for_future_date(): void
    {
        $teacher = User::where('email', 'teacher@school.test')->first();
        $section = Section::first();

        if (! $teacher || ! $section) {
            $this->markTestSkipped('Demo seeder data required.');
        }

        $futureDate = now()->addDays(5)->toDateString();
        $marker = new MarkAttendance;

        $this->expectException(InvalidArgumentException::class);
        $marker->execute($section, $futureDate, [], $teacher);
    }

    public function test_marking_attendance_updates_records_and_day_counts(): void
    {
        $teacher = User::where('email', 'teacher@school.test')->first();
        $section = Section::whereHas('enrollments')->first();

        if (! $teacher || ! $section) {
            $this->markTestSkipped('Demo seeder data required.');
        }

        $enrollments = Enrollment::where('section_id', $section->id)->where('status', 'active')->take(3)->get();
        if ($enrollments->count() < 2) {
            $this->markTestSkipped('Enrollments required.');
        }

        $records = [
            $enrollments[0]->id => 'present',
            $enrollments[1]->id => 'absent',
        ];

        $today = WorkingCalendar::schoolToday()->toDateString();
        $marker = new MarkAttendance;
        $attDay = $marker->execute($section, $today, $records, $teacher);

        $this->assertNotNull($attDay);
        $this->assertEquals(1, $attDay->present_count);
        $this->assertEquals(1, $attDay->absent_count);

        // Verify automated absent parent alert in notification logs (Doc 6 & Doc 9)
        $this->assertDatabaseHas('notification_logs', [
            'template_key' => 'attendance_absent',
        ]);
    }
}
