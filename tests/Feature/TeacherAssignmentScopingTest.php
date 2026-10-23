<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Tests\TestCase;

class TeacherAssignmentScopingTest extends TestCase
{
    protected ?User $teacherUser;

    protected ?User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacherUser = User::where('email', 'teacher@school.test')->first();
        $this->adminUser = User::where('email', 'superadmin@school.test')->first();
    }

    public function test_teacher_academic_setup_shows_assigned_classes_and_blocks_mutation(): void
    {
        if (! $this->teacherUser) {
            $this->markTestSkipped('Teacher user not found.');
        }

        $this->actingAs($this->teacherUser);

        // Teacher can view academic index
        $response = $this->get(route('academic.index'));
        $response->assertStatus(200);
        $response->assertSee('My Class');
        $response->assertSee('Teaching Assignments');

        // Teacher cannot see or execute administrative modifications
        $response->assertDontSee('+ Add Year');
        $response->assertDontSee('Add New Class');
        $response->assertDontSee('Assign Teacher to Subject');

        // Attempting to post new academic year returns 403
        $postYear = $this->post(route('academic.years.store'), [
            'name' => '2028-29',
            'starts_on' => '2028-04-01',
            'ends_on' => '2029-03-31',
        ]);
        $postYear->assertStatus(403);

        // Attempting to post new class returns 403
        $postClass = $this->post(route('academic.classes.store'), [
            'name' => 'Class 99',
            'numeric_level' => 99,
        ]);
        $postClass->assertStatus(403);

        // Attempting to assign teacher returns 403
        $postAssign = $this->post(route('academic.assign-teacher'), [
            'section_id' => 1,
            'subject_id' => 1,
            'staff_id' => 1,
            'academic_year_id' => AcademicYear::current()?->id,
        ]);
        $postAssign->assertStatus(403);
    }

    public function test_teacher_can_only_view_students_in_assigned_sections(): void
    {
        if (! $this->teacherUser || ! $this->teacherUser->staff) {
            $this->markTestSkipped('Teacher or staff profile not found.');
        }

        $this->actingAs($this->teacherUser);

        $visibleSectionIds = $this->teacherUser->staff->visibleSectionIds();
        $assignedSectionId = $visibleSectionIds->first();

        // Find an unassigned section
        $unassignedSection = Section::whereNotIn('id', $visibleSectionIds)->first();

        // Listing page
        $response = $this->get(route('students.index'));
        $response->assertStatus(200);

        // Filter by assigned section works
        if ($assignedSectionId) {
            $resAssigned = $this->get(route('students.index', ['section_id' => $assignedSectionId]));
            $resAssigned->assertStatus(200);
        }

        // Filtering by unassigned section returns 403 Forbidden
        if ($unassignedSection) {
            $resUnassigned = $this->get(route('students.index', ['section_id' => $unassignedSection->id]));
            $resUnassigned->assertStatus(403);
        }

        // Assigned student profile works
        $assignedStudent = Student::whereHas('enrollments', fn ($q) => $q->whereIn('section_id', $visibleSectionIds))->first();
        if ($assignedStudent) {
            $this->get(route('students.show', $assignedStudent))->assertStatus(200);
        }

        // Unassigned student profile returns 403
        if ($unassignedSection) {
            $unassignedStudent = Student::create([
                'admission_no' => uniqid('ADM-'),
                'first_name' => 'Unassigned',
                'last_name' => 'Student',
                'gender' => 'female',
                'date_of_birth' => '2016-01-01',
                'status' => 'active',
            ]);
            Enrollment::create([
                'student_id' => $unassignedStudent->id,
                'section_id' => $unassignedSection->id,
                'academic_year_id' => AcademicYear::current()?->id,
                'enrolled_on' => now()->toDateString(),
                'status' => 'active',
            ]);

            $this->get(route('students.show', $unassignedStudent))->assertStatus(403);
        }
    }

    public function test_teacher_attendance_is_strictly_scoped_to_assigned_sections(): void
    {
        if (! $this->teacherUser || ! $this->teacherUser->staff) {
            $this->markTestSkipped('Teacher not found.');
        }

        $this->actingAs($this->teacherUser);

        $visibleSectionIds = $this->teacherUser->staff->visibleSectionIds();
        $unassignedSection = Section::whereNotIn('id', $visibleSectionIds)->first();

        if ($unassignedSection) {
            // Cannot post attendance for unassigned section
            $saveRes = $this->post(route('attendance.save'), [
                'section_id' => $unassignedSection->id,
                'date' => now()->toDateString(),
                'attendance' => [9999 => 'present'],
            ]);
            $saveRes->assertStatus(403);

            // Cannot view attendance reports for unassigned section
            $reportRes = $this->get(route('attendance.reports', ['section_id' => $unassignedSection->id]));
            $reportRes->assertStatus(403);
        }
    }

    public function test_teacher_exam_marks_and_reports_are_scoped_to_assigned_sections(): void
    {
        if (! $this->teacherUser || ! $this->teacherUser->staff) {
            $this->markTestSkipped('Teacher not found.');
        }

        $this->actingAs($this->teacherUser);

        $visibleSectionIds = $this->teacherUser->staff->visibleSectionIds();
        $unassignedSection = Section::whereNotIn('id', $visibleSectionIds)->first();
        $exam = Exam::first();

        if ($exam && $unassignedSection) {
            // Consolidated marksheet of unassigned section returns 403
            $cmRes = $this->get(route('exams.class-marksheet', ['exam' => $exam, 'section_id' => $unassignedSection->id]));
            $cmRes->assertStatus(403);

            // Bulk marksheets zip of unassigned section returns 403
            $bmRes = $this->get(route('exams.bulk-marksheets', ['exam' => $exam, 'section_id' => $unassignedSection->id]));
            $bmRes->assertStatus(403);

            // Entering marks for enrollment in unassigned section returns 403
            $examSubject = $exam->examSubjects()->first();
            $dummyStudent = Student::create([
                'admission_no' => uniqid('ADM-'),
                'first_name' => 'Dummy',
                'last_name' => 'Student',
                'gender' => 'male',
                'date_of_birth' => '2016-01-01',
                'status' => 'active',
            ]);
            $unassignedEnrollment = Enrollment::create([
                'student_id' => $dummyStudent->id,
                'section_id' => $unassignedSection->id,
                'academic_year_id' => AcademicYear::current()?->id,
                'enrolled_on' => now()->toDateString(),
                'status' => 'active',
            ]);

            if ($examSubject) {
                $saveMarksRes = $this->post(route('exams.save-marks', ['exam' => $exam, 'examSubject' => $examSubject]), [
                    'marks' => [
                        $unassignedEnrollment->id => 85,
                    ],
                ]);
                $saveMarksRes->assertStatus(403);
            }
        }
    }
}
