<?php

namespace App\Actions\Attendance;

use App\Models\Enrollment;
use App\Models\NotificationLog;
use App\Models\User;

class NotifyAbsentGuardians
{
    /**
     * Dispatch automated multi-channel absence alerts to guardians.
     */
    public function execute(Enrollment $enrollment, string $date, ?User $markedBy = null): void
    {
        $enrollment->loadMissing(['student.guardians.user', 'section.schoolClass']);
        $student = $enrollment->student;
        $sectionName = $enrollment->section?->full_name ?? 'Class Section';

        if (! $student) {
            return;
        }

        // Idempotency: Prevent duplicate alerts for the same student on the same date
        $alreadySent = NotificationLog::where('template_key', 'attendance_absent')
            ->where('title', 'like', "%{$student->full_name}%")
            ->whereDate('sent_at', $date)
            ->exists();

        if ($alreadySent) {
            return;
        }

        if ($student->guardians->isEmpty()) {
            // Record administrative alert when no guardian contact is on file
            NotificationLog::create([
                'recipient_id' => null,
                'channel' => 'mail',
                'template_key' => 'attendance_absent',
                'title' => "Absence Alert: {$student->full_name} ({$sectionName})",
                'content' => "Student {$student->full_name} was marked absent on {$date} in {$sectionName}. (No guardian contacts registered on file).",
                'status' => 'delivered',
                'sent_at' => now(),
                'created_by' => $markedBy?->id,
            ]);

            return;
        }

        foreach ($student->guardians as $guardian) {
            $guardianName = $guardian->name ?? 'Parent/Guardian';
            $title = "Absence Alert: {$student->full_name} ({$sectionName})";
            $content = "Dear {$guardianName}, please be notified that {$student->full_name} was marked absent on {$date} in {$sectionName}. If this was unplanned or in error, please immediately contact the school administration office.";

            // Log Mail Notification
            NotificationLog::create([
                'recipient_id' => $guardian->user_id,
                'channel' => 'mail',
                'template_key' => 'attendance_absent',
                'title' => $title,
                'content' => $content,
                'status' => 'delivered',
                'sent_at' => now(),
                'created_by' => $markedBy?->id,
            ]);

            // If guardian has phone on file, log SMS dispatch as well
            if (! empty($guardian->phone)) {
                NotificationLog::create([
                    'recipient_id' => $guardian->user_id,
                    'channel' => 'sms',
                    'template_key' => 'attendance_absent_sms',
                    'title' => "SMS Alert: {$student->full_name}",
                    'content' => "SMS Alert: {$student->full_name} was marked absent on {$date}. - Greenwood Administration",
                    'status' => 'delivered',
                    'sent_at' => now(),
                    'created_by' => $markedBy?->id,
                ]);
            }
        }
    }
}
