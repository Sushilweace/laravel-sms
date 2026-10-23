<?php

namespace App\Support;

use App\Models\Exam;
use App\Models\Grade;
use App\Models\GradeScale;
use App\Models\Mark;

class GradeCalculator
{
    public static function calculateForEnrollment(Exam $exam, int $enrollmentId): array
    {
        $examSubjects = $exam->examSubjects;
        $totalMarks = '0.00';
        $maxMarks = '0.00';
        $failedSubjects = 0;
        $hasAbsent = false;

        $marks = Mark::whereIn('exam_subject_id', $examSubjects->pluck('id'))
            ->where('enrollment_id', $enrollmentId)
            ->get()
            ->keyBy('exam_subject_id');

        foreach ($examSubjects as $subject) {
            $maxMarks = bcadd($maxMarks, (string) $subject->max_marks, 2);
            $mark = $marks->get($subject->id);

            if (! $mark || $mark->is_absent) {
                $hasAbsent = true;
                $failedSubjects++;
            } else {
                $effective = $mark->effective_marks ?? '0.00';
                $totalMarks = bcadd($totalMarks, $effective, 2);

                if (bccomp($effective, (string) $subject->pass_marks, 2) === -1) {
                    $failedSubjects++;
                }
            }
        }

        $percentage = '0.00';
        if (bccomp($maxMarks, '0.00', 2) > 0) {
            $percentage = bcdiv(bcmul($totalMarks, '100', 4), $maxMarks, 2);
        }

        $passPercentage = (string) ($exam->pass_percentage ?? '33.00');
        $isOverallPass = bccomp($percentage, $passPercentage, 2) >= 0;
        $resultStatus = ($failedSubjects === 0 && $isOverallPass) ? 'pass' : 'fail';

        $gradeInfo = self::determineGrade($percentage, $exam->grade_scale_id);

        return [
            'total_marks' => $totalMarks,
            'max_marks' => $maxMarks,
            'percentage' => $percentage,
            'failed_subjects' => $failedSubjects,
            'result_status' => $resultStatus,
            'grade' => $gradeInfo['grade'],
            'gpa' => $gradeInfo['grade_point'],
        ];
    }

    public static function determineGrade(string $percentage, ?int $gradeScaleId = null): array
    {
        $scale = $gradeScaleId
            ? GradeScale::find($gradeScaleId)
            : (GradeScale::where('is_default', true)->first() ?? GradeScale::first());

        if (! $scale) {
            return ['grade' => 'N/A', 'grade_point' => 0.00];
        }

        $pct = (float) $percentage;
        $matchingGrade = Grade::where('grade_scale_id', $scale->id)
            ->where('min_percentage', '<=', $pct)
            ->where('max_percentage', '>=', $pct)
            ->first();

        if (! $matchingGrade) {
            $matchingGrade = Grade::where('grade_scale_id', $scale->id)
                ->orderBy('min_percentage', 'asc')
                ->first();
        }

        return [
            'grade' => $matchingGrade ? $matchingGrade->name : 'N/A',
            'grade_point' => $matchingGrade ? (float) $matchingGrade->grade_point : 0.00,
        ];
    }
}
