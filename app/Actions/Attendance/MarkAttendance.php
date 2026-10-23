<?php

namespace App\Actions\Attendance;

use App\Models\Attendance;
use App\Models\AttendanceDay;
use App\Models\AttendanceSummary;
use App\Models\Enrollment;
use App\Models\Section;
use App\Models\Setting;
use App\Models\User;
use App\Support\AttendancePercentage;
use App\Support\WorkingCalendar;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class MarkAttendance
{
    public function execute(Section $section, string $date, array $records, User $user, ?string $editReason = null): AttendanceDay
    {
        $targetDateStr = Carbon::parse($date)->toDateString();
        $todayStr = WorkingCalendar::schoolToday()->toDateString();

        if ($targetDateStr > $todayStr) {
            throw new InvalidArgumentException('Cannot mark attendance for a future date.');
        }

        $targetDate = Carbon::parse($targetDateStr);
        $today = Carbon::parse($todayStr);

        if (! WorkingCalendar::isWorkingDay($targetDate)) {
            throw new InvalidArgumentException('Cannot mark attendance on a holiday or non-working day.');
        }

        $editDaysWindow = (int) Setting::get('attendance.teacher_edit_days', 2);
        $diffDays = $targetDate->diffInDays($today, false);

        if ($diffDays > $editDaysWindow) {
            if (! $user->can('attendance.edit-past')) {
                throw new InvalidArgumentException("Marking attendance older than {$editDaysWindow} days requires special permission.");
            }
            if (empty($editReason)) {
                throw new InvalidArgumentException('An edit reason is required when modifying past attendance.');
            }
        }

        $activeEnrollments = Enrollment::where('section_id', $section->id)
            ->where('status', 'active')
            ->pluck('id')
            ->toArray();

        return DB::transaction(function () use ($section, $targetDate, $targetDateStr, $records, $user, $editReason, $activeEnrollments) {
            $presentCount = 0;
            $absentCount = 0;
            $lateCount = 0;
            $leaveCount = 0;

            foreach ($records as $enrollmentId => $status) {
                if (! in_array($enrollmentId, $activeEnrollments)) {
                    continue; // Skip any tampered enrollment id not in this section
                }

                $status = in_array($status, ['present', 'absent', 'late', 'leave']) ? $status : 'present';

                switch ($status) {
                    case 'present': $presentCount++;
                        break;
                    case 'absent': $absentCount++;
                        break;
                    case 'late': $lateCount++;
                        break;
                    case 'leave': $leaveCount++;
                        break;
                }

                $existing = Attendance::where('enrollment_id', $enrollmentId)
                    ->where(function ($q) use ($targetDateStr) {
                        $q->where('date', $targetDateStr)
                            ->orWhere('date', 'like', "{$targetDateStr}%");
                    })
                    ->first();

                if ($existing) {
                    $existing->update([
                        'status' => $status,
                        'source' => 'manual',
                        'edited_reason' => $editReason,
                        'last_edited_by' => $user->id,
                    ]);
                } else {
                    Attendance::create([
                        'enrollment_id' => $enrollmentId,
                        'date' => $targetDateStr,
                        'status' => $status,
                        'source' => 'manual',
                        'edited_reason' => $editReason,
                        'last_edited_by' => $user->id,
                    ]);
                }

                $this->updateSummary($enrollmentId, $targetDate);
            }

            $attDay = AttendanceDay::where('section_id', $section->id)
                ->where(function ($q) use ($targetDateStr) {
                    $q->where('date', $targetDateStr)
                        ->orWhere('date', 'like', "{$targetDateStr}%");
                })
                ->first();

            $dayData = [
                'marked_by' => $user->id,
                'finalized_at' => now(),
                'present_count' => $presentCount,
                'absent_count' => $absentCount,
                'late_count' => $lateCount,
                'leave_count' => $leaveCount,
            ];

            if ($attDay) {
                $attDay->update($dayData);

                return $attDay;
            }

            return AttendanceDay::create(array_merge([
                'section_id' => $section->id,
                'date' => $targetDateStr,
            ], $dayData));
        });
    }

    private function updateSummary(int $enrollmentId, Carbon $date): void
    {
        $startOfMonth = $date->copy()->startOfMonth()->toDateString();
        $endOfMonth = $date->copy()->endOfMonth()->toDateString();

        $attendances = Attendance::where('enrollment_id', $enrollmentId)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get();

        $present = $attendances->where('status', 'present')->count();
        $absent = $attendances->where('status', 'absent')->count();
        $late = $attendances->where('status', 'late')->count();
        $leave = $attendances->where('status', 'leave')->count();
        $workingDays = $attendances->count();

        $percentage = AttendancePercentage::calculate($present, $late, $absent, $leave);

        $sum = AttendanceSummary::where('enrollment_id', $enrollmentId)
            ->where(function ($q) use ($startOfMonth) {
                $q->where('month', $startOfMonth)
                    ->orWhere('month', 'like', "{$startOfMonth}%");
            })
            ->first();

        $sumData = [
            'working_days' => $workingDays,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'leave' => $leave,
            'percentage' => $percentage,
        ];

        if ($sum) {
            $sum->update($sumData);
        } else {
            AttendanceSummary::create(array_merge([
                'enrollment_id' => $enrollmentId,
                'month' => $startOfMonth,
            ], $sumData));
        }
    }
}
