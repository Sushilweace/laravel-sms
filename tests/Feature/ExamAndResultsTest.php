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

    public function test_class_marksheet_web_view_and_downloads(): void
    {
        $principal = User::where('email', 'principal@school.test')->first();
        $publishedExam = Exam::where('status', 'published')->first();

        if (! $principal || ! $publishedExam) {
            $this->markTestSkipped('Principal and published exam required.');
        }

        // 1. Web View
        $response = $this->actingAs($principal)->get("/exams/{$publishedExam->id}/class-marksheet");
        $response->assertStatus(200);
        $response->assertSee('Consolidated Class Marksheet');
        $response->assertSee('Download Marksheet (PDF)');

        // 2. PDF Download
        $pdfResp = $this->actingAs($principal)->get("/exams/{$publishedExam->id}/class-marksheet?download=1");
        $pdfResp->assertStatus(200);
        $pdfResp->assertHeader('content-type', 'application/pdf');

        // 3. CSV Export
        $csvResp = $this->actingAs($principal)->get("/exams/{$publishedExam->id}/class-marksheet?format=csv");
        $csvResp->assertStatus(200);
        $this->assertStringContainsString('text/csv', $csvResp->headers->get('content-type'));
    }

    public function test_subject_marksheet_downloads(): void
    {
        $teacher = User::where('email', 'teacher@school.test')->first();
        $exam = Exam::first();
        $examSubject = $exam?->examSubjects()->first();

        if (! $teacher || ! $exam || ! $examSubject) {
            $this->markTestSkipped('Teacher, exam and subject required.');
        }

        // PDF Download
        $pdfResp = $this->actingAs($teacher)->get("/exams/{$exam->id}/subject/{$examSubject->id}/download-marksheet?download=1");
        $pdfResp->assertStatus(200);
        $pdfResp->assertHeader('content-type', 'application/pdf');

        // CSV Download
        $csvResp = $this->actingAs($teacher)->get("/exams/{$exam->id}/subject/{$examSubject->id}/download-marksheet?format=csv");
        $csvResp->assertStatus(200);
        $this->assertStringContainsString('text/csv', $csvResp->headers->get('content-type'));
    }

    public function test_individual_student_marksheet_download(): void
    {
        $principal = User::where('email', 'principal@school.test')->first();
        $publishedExam = Exam::where('status', 'published')->first();
        $result = Result::where('exam_id', $publishedExam?->id)->first();

        if (! $principal || ! $publishedExam || ! $result) {
            $this->markTestSkipped('Principal, published exam, and result required.');
        }

        $response = $this->actingAs($principal)->get("/exams/{$publishedExam->id}/marksheet/{$result->enrollment_id}?download=1");
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_bulk_student_marksheets_zip_download(): void
    {
        $principal = User::where('email', 'principal@school.test')->first();
        $publishedExam = Exam::where('status', 'published')->first();

        if (! $principal || ! $publishedExam) {
            $this->markTestSkipped('Principal and published exam required.');
        }

        $response = $this->actingAs($principal)->get("/exams/{$publishedExam->id}/bulk-marksheets");
        $response->assertStatus(200);
        $this->assertStringContainsString('application/zip', $response->headers->get('content-type'));
        $this->assertStringContainsString('.zip', $response->headers->get('content-disposition'));
    }
}
