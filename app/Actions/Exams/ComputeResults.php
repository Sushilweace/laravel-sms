<?php

namespace App\Actions\Exams;

use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\Result;
use App\Support\GradeCalculator;
use Illuminate\Support\Facades\DB;

class ComputeResults
{
    public function execute(Exam $exam): void
    {
        DB::transaction(function () use ($exam) {
            // Find all classes involved in this exam
            $classIds = $exam->examSubjects()->pluck('school_class_id')->unique();

            // Find all active enrollments for these classes in the exam's academic year
            $enrollments = Enrollment::where('academic_year_id', $exam->academic_year_id)
                ->where('status', 'active')
                ->whereHas('section', function ($q) use ($classIds) {
                    $q->whereIn('school_class_id', $classIds);
                })
                ->with(['section'])
                ->get();

            // Compute result for each enrollment
            foreach ($enrollments as $enrollment) {
                $calc = GradeCalculator::calculateForEnrollment($exam, $enrollment->id);

                Result::updateOrCreate(
                    [
                        'exam_id' => $exam->id,
                        'enrollment_id' => $enrollment->id,
                    ],
                    [
                        'total_marks' => $calc['total_marks'],
                        'max_marks' => $calc['max_marks'],
                        'percentage' => $calc['percentage'],
                        'grade' => $calc['grade'],
                        'gpa' => $calc['gpa'],
                        'result_status' => $calc['result_status'],
                        'failed_subjects' => $calc['failed_subjects'],
                    ]
                );
            }

            // Assign Section Ranks and Class Ranks with rank-skipping ties
            foreach ($classIds as $classId) {
                // Class Rank
                $classResults = Result::where('exam_id', $exam->id)
                    ->whereHas('enrollment.section', fn ($q) => $q->where('school_class_id', $classId))
                    ->orderByDesc('percentage')
                    ->get();

                $this->assignRanks($classResults, 'class_rank');

                // Section Rank for each section of this class
                $sections = $enrollments->where('section.school_class_id', $classId)->pluck('section_id')->unique();
                foreach ($sections as $sectionId) {
                    $sectionResults = Result::where('exam_id', $exam->id)
                        ->whereHas('enrollment', fn ($q) => $q->where('section_id', $sectionId))
                        ->orderByDesc('percentage')
                        ->get();

                    $this->assignRanks($sectionResults, 'section_rank');
                }
            }

            $exam->update([
                'results_computed_at' => now(),
            ]);
        });
    }

    private function assignRanks($results, string $rankField): void
    {
        $currentRank = 1;
        $prevPercentage = null;
        $index = 0;

        foreach ($results as $res) {
            $index++;
            if ($prevPercentage !== null && bccomp((string) $res->percentage, (string) $prevPercentage, 2) === 0) {
                // Same rank as previous
            } else {
                $currentRank = $index;
            }

            $res->update([$rankField => $currentRank]);
            $prevPercentage = $res->percentage;
        }
    }
}
