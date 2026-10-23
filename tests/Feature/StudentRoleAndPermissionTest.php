<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Tests\TestCase;

class StudentRoleAndPermissionTest extends TestCase
{
    protected ?User $studentUser;

    protected ?User $accountantUser;

    protected ?User $teacherUser;

    protected ?User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->studentUser = User::where('email', 'student@school.test')->first();
        $this->accountantUser = User::where('email', 'accountant@school.test')->first();
        $this->teacherUser = User::where('email', 'teacher@school.test')->first();
        $this->adminUser = User::where('email', 'superadmin@school.test')->first();
    }

    public function test_student_sees_only_personal_fee_portal(): void
    {
        if (! $this->studentUser) {
            $this->markTestSkipped('Student user not found in demo seed.');
        }

        $this->actingAs($this->studentUser);

        $response = $this->get(route('fees.index'));
        $response->assertStatus(200);
        $response->assertViewIs('fees.student');

        // Verify student sees their own name and personal metrics
        $response->assertSee('Student Fee Account');
        $response->assertSee('Total Billed');
        $response->assertSee('Remaining Balance Due');

        // Verify student does NOT see admin fee management controls
        $response->assertDontSee('Fee Heads Catalog');
        $response->assertDontSee('Add Fee Head');
        $response->assertDontSee('Assign Fee to Class');
    }

    public function test_student_cannot_view_all_defaulters_list(): void
    {
        if (! $this->studentUser) {
            $this->markTestSkipped('Student user not found.');
        }

        $this->actingAs($this->studentUser);

        // Student requesting defaulters list must get 403 Forbidden
        $response = $this->get(route('fees.defaulters'));
        $response->assertStatus(403);
    }

    public function test_accountant_can_view_all_defaulters_list(): void
    {
        if (! $this->accountantUser) {
            $this->markTestSkipped('Accountant user not found.');
        }

        $this->actingAs($this->accountantUser);

        $response = $this->get(route('fees.defaulters'));
        $response->assertStatus(200);
        $response->assertViewIs('fees.defaulters');
        $response->assertSee('Overdue Fee Accounts');
    }

    public function test_student_cannot_perform_fee_administrative_actions(): void
    {
        if (! $this->studentUser) {
            $this->markTestSkipped('Student user not found.');
        }

        $this->actingAs($this->studentUser);

        // Cannot create fee head
        $resHead = $this->post(route('fees.heads.store'), ['name' => 'Hacker Fee']);
        $resHead->assertStatus(403);

        // Cannot generate invoices
        $resGen = $this->post(route('fees.invoices.generate'), ['period_key' => '2026-05']);
        $resGen->assertStatus(403);

        // Cannot access collect fee screen
        $invoice = Invoice::first();
        if ($invoice) {
            $resCollect = $this->get(route('fees.collect', $invoice));
            $resCollect->assertStatus(403);
        }
    }

    public function test_student_attendance_view_is_scoped_to_self(): void
    {
        if (! $this->studentUser) {
            $this->markTestSkipped('Student user not found.');
        }

        $this->actingAs($this->studentUser);

        $response = $this->get(route('attendance.index'));
        $response->assertStatus(200);
        $response->assertViewIs('attendance.student');
        $response->assertSee('Student Attendance');
        $response->assertSee('Daily Attendance History');

        // Cannot mark attendance
        $postRes = $this->post(route('attendance.save'), []);
        $postRes->assertStatus(403);

        // Cannot view school-wide attendance reports
        $reportRes = $this->get(route('attendance.reports'));
        $reportRes->assertStatus(403);
    }

    public function test_student_exam_view_shows_personal_marksheets(): void
    {
        if (! $this->studentUser) {
            $this->markTestSkipped('Student user not found.');
        }

        $this->actingAs($this->studentUser);

        $response = $this->get(route('exams.index'));
        $response->assertStatus(200);
        $response->assertViewIs('exams.student');
        $response->assertSee('Examination Results');

        // Cannot create exam
        $createRes = $this->get(route('exams.create'));
        $createRes->assertStatus(403);
    }

    public function test_student_cannot_access_staff_and_admin_modules(): void
    {
        if (! $this->studentUser) {
            $this->markTestSkipped('Student user not found.');
        }

        $this->actingAs($this->studentUser);

        // Academic setup
        $this->get(route('academic.index'))->assertStatus(403);

        // Staff directory
        $this->get(route('staff.index'))->assertStatus(403);

        // AI Assistant
        $this->get(route('ai.index'))->assertStatus(403);

        // Admin logs & schools
        $this->get(route('admin.schools.index'))->assertStatus(403);
        $this->get(route('admin.logs.errors'))->assertStatus(403);
        $this->get(route('admin.logs.mail'))->assertStatus(403);
    }

    public function test_student_redirected_to_own_profile_from_students_index(): void
    {
        if (! $this->studentUser) {
            $this->markTestSkipped('Student user not found.');
        }

        $this->actingAs($this->studentUser);

        $student = $this->studentUser->student ?? Student::where('user_id', $this->studentUser->id)->first();
        $response = $this->get(route('students.index'));

        if ($student) {
            $response->assertRedirect(route('students.show', $student));
        }

        // Student cannot admit or promote
        $this->get(route('students.create'))->assertStatus(403);
        $this->get(route('students.promote'))->assertStatus(403);
    }

    public function test_student_receipt_download_security(): void
    {
        if (! $this->studentUser) {
            $this->markTestSkipped('Student user not found.');
        }

        $student = $this->studentUser->student ?? Student::where('user_id', $this->studentUser->id)->first();
        if (! $student) {
            $this->markTestSkipped('Student model not found.');
        }

        // Find own invoices and payments
        $ownInvoices = Invoice::visibleTo($this->studentUser)->get();
        $ownPayment = Payment::whereIn('invoice_id', $ownInvoices->pluck('id'))->first();

        if ($ownPayment) {
            $this->actingAs($this->studentUser);
            $res = $this->get(route('fees.receipt', $ownPayment));
            $res->assertStatus(200);
        }

        // Payment belonging to another student
        $otherPayment = Payment::whereNotIn('invoice_id', $ownInvoices->pluck('id'))->first();
        if ($otherPayment) {
            $this->actingAs($this->studentUser);
            $resOther = $this->get(route('fees.receipt', $otherPayment));
            $resOther->assertStatus(403);
        }
    }
}
