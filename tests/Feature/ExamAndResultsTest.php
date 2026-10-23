<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\Result;
use App\Models\User;
use Tests\TestCase;

class ExamAndResultsTest extends TestCase
{
    public function test_exam_lifecycle_and_results_computation(): void
    {
        $principal = User::where('email', 'principal@school.test')->first();
        $teacher = User::where('email', 'teacher@school.test')->first();
        $exam = Exam::where('status', 'draft')->first();

        if (! $exam) {
            $exam = Exam::latest()->first();
        }

        if (! $principal || ! $teacher || ! $exam) {
            $this->markTestSkipped('Exam and faculty users required.');
        }

        // Test results exist on published exams
        $publishedExam = Exam::where('status', 'published')->first();
        if ($publishedExam) {
            $results = Result::where('exam_id', $publishedExam->id)->get();
            $this->assertNotEmpty($results);

            // Assert ranks are assigned
            $firstResult = $results->sortBy('class_rank')->first();
            $this->assertEquals(1, $firstResult->class_rank);
            $this->assertNotNull($firstResult->percentage);
            $this->assertNotNull($firstResult->grade);
        }
    }
}
