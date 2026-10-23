<?php

namespace App\Actions\Exams;

use App\Models\ExamSubject;
use App\Models\Mark;
use App\Models\MarkChange;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class EnterMarks
{
    public function execute(ExamSubject $examSubject, int $enrollmentId, ?float $marksObtained, bool $isAbsent, float $grace = 0.0, ?string $graceReason = null, ?string $remarks = null, ?User $user = null, ?string $changeReason = null): Mark
    {
        $exam = $examSubject->exam;

        if ($exam->isDraft()) {
            throw new InvalidArgumentException('Marks cannot be entered while exam is in draft.');
        }

        if ($exam->isLocked() || $exam->isPublished()) {
            if (! $user || ! $user->can('marks.edit-locked')) {
                throw new InvalidArgumentException('Editing marks for a locked or published exam requires special permission.');
            }
            if (empty($changeReason)) {
                throw new InvalidArgumentException('A reason is required when editing marks for a locked or published exam.');
            }
        }

        if (! $isAbsent && $marksObtained !== null) {
            if ($marksObtained < 0 || $marksObtained > (float) $examSubject->max_marks) {
                throw new InvalidArgumentException("Marks obtained must be between 0 and {$examSubject->max_marks}.");
            }
        }

        return DB::transaction(function () use ($examSubject, $enrollmentId, $marksObtained, $isAbsent, $grace, $graceReason, $remarks, $user, $changeReason, $exam) {
            $existing = Mark::where('exam_subject_id', $examSubject->id)
                ->where('enrollment_id', $enrollmentId)
                ->first();

            if ($existing && ($exam->isLocked() || $exam->isPublished()) && $changeReason) {
                MarkChange::create([
                    'mark_id' => $existing->id,
                    'old_marks' => $existing->marks_obtained,
                    'new_marks' => $isAbsent ? null : $marksObtained,
                    'reason' => $changeReason,
                    'changed_by' => $user->id,
                ]);
            }

            return Mark::updateOrCreate(
                [
                    'exam_subject_id' => $examSubject->id,
                    'enrollment_id' => $enrollmentId,
                ],
                [
                    'marks_obtained' => $isAbsent ? null : $marksObtained,
                    'is_absent' => $isAbsent,
                    'grace' => $grace,
                    'grace_reason' => $graceReason,
                    'teacher_remarks' => $remarks,
                    'entered_by' => $existing ? $existing->entered_by : ($user?->id),
                    'updated_by' => $user?->id,
                ]
            );
        });
    }
}
