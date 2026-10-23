<?php

namespace Database\Seeders;

use App\Actions\Academic\SetCurrentAcademicYear;
use App\Actions\Attendance\MarkAttendance;
use App\Actions\Exams\ComputeResults;
use App\Actions\Exams\EnterMarks;
use App\Actions\Exams\LockExam;
use App\Actions\Exams\OpenMarksEntry;
use App\Actions\Exams\PublishResults;
use App\Actions\Fees\CollectPayment;
use App\Actions\Fees\GenerateInvoices;
use App\Actions\Notices\PublishNotice;
use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\FeeHead;
use App\Models\FeeStructure;
use App\Models\Grade;
use App\Models\GradeScale;
use App\Models\Guardian;
use App\Models\Invoice;
use App\Models\School;
use App\Models\SchoolCalendarDay;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Setting;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use App\Support\WorkingCalendar;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password123');

        // 1. School Profile & Settings
        $school = School::create([
            'name' => 'Greenwood International School',
            'code' => 'GIS-2026',
            'address' => '104 Education Enclave, Knowledge City, Bangalore, India',
            'phone' => '+91 98765 43210',
            'email' => 'contact@greenwood.test',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'package_tier' => 'enterprise',
            'billing_cycle' => 'monthly',
        ]);

        Setting::set('school.timezone', 'Asia/Kolkata');
        Setting::set('school.currency', 'INR');
        Setting::set('attendance.teacher_edit_days', 2);
        Setting::set('attendance.low_threshold', 75);
        Setting::set('attendance.default_status', 'present');

        // 2. Users with Roles
        $superAdminUser = User::create([
            'name' => 'Alexander Pierce',
            'email' => 'superadmin@school.test',
            'phone' => '+91 99999 00001',
            'user_type' => 'admin',
            'password' => $password,
        ]);
        $superAdminUser->assignRole('Super Admin');

        $principalUser = User::create([
            'name' => 'Dr. Eleanor Vance',
            'email' => 'principal@school.test',
            'phone' => '+91 99999 00002',
            'user_type' => 'staff',
            'password' => $password,
        ]);
        $principalUser->assignRole('School Admin');

        $teacherUser = User::create([
            'name' => 'Marcus Holloway',
            'email' => 'teacher@school.test',
            'phone' => '+91 99999 00003',
            'user_type' => 'staff',
            'password' => $password,
        ]);
        $teacherUser->assignRole('Teacher');

        $accountantUser = User::create([
            'name' => 'Sarah Connor',
            'email' => 'accountant@school.test',
            'phone' => '+91 99999 00004',
            'user_type' => 'staff',
            'password' => $password,
        ]);
        $accountantUser->assignRole('Accountant');

        $parentUser = User::create([
            'name' => 'Rajesh Sharma',
            'email' => 'parent@school.test',
            'phone' => '+91 98765 11111',
            'user_type' => 'guardian',
            'password' => $password,
        ]);
        $parentUser->assignRole('Parent');

        $studentUser = User::create([
            'name' => 'Aarav Sharma',
            'email' => 'student@school.test',
            'phone' => '+91 98765 22222',
            'user_type' => 'student',
            'password' => $password,
        ]);
        $studentUser->assignRole('Student');

        // 3. Academic Years
        $pastYear = AcademicYear::create([
            'name' => '2025-26',
            'starts_on' => '2025-04-01',
            'ends_on' => '2026-03-31',
            'is_current' => false,
            'is_locked' => true,
        ]);

        $currentYear = AcademicYear::create([
            'name' => '2026-27',
            'starts_on' => '2026-04-01',
            'ends_on' => '2027-03-31',
            'is_current' => true,
            'is_locked' => false,
        ]);

        (new SetCurrentAcademicYear)->execute($currentYear);

        // 4. Classes (Class 1 to Class 10 with promotion next_class_id)
        $classes = [];
        $prevClass = null;
        for ($i = 10; $i >= 1; $i--) {
            $class = SchoolClass::create([
                'name' => "Class {$i}",
                'numeric_level' => $i,
                'next_class_id' => $prevClass?->id,
            ]);
            $classes[$i] = $class;
            $prevClass = $class;
        }

        // 5. Subjects
        $subjects = [
            'ENG' => Subject::create(['name' => 'English Language', 'code' => 'ENG', 'type' => 'theory']),
            'MAT' => Subject::create(['name' => 'Mathematics', 'code' => 'MAT', 'type' => 'theory']),
            'SCI' => Subject::create(['name' => 'Science', 'code' => 'SCI', 'type' => 'both']),
            'SST' => Subject::create(['name' => 'Social Studies', 'code' => 'SST', 'type' => 'theory']),
            'HIN' => Subject::create(['name' => 'Hindi', 'code' => 'HIN', 'type' => 'theory']),
            'CS' => Subject::create(['name' => 'Computer Science', 'code' => 'CS', 'type' => 'both']),
        ];

        // Map subjects to Class 5 and other classes
        foreach ($classes as $cl) {
            foreach ($subjects as $sb) {
                $cl->subjects()->attach($sb->id, ['is_optional' => false]);
            }
        }

        // 6. Staff Profiles
        $principalStaff = Staff::create([
            'user_id' => $principalUser->id,
            'employee_code' => 'EMP-001',
            'first_name' => 'Eleanor',
            'last_name' => 'Vance',
            'designation' => 'Principal',
            'phone' => '+91 99999 00002',
            'email' => 'principal@school.test',
            'joining_date' => '2020-06-01',
            'status' => 'active',
        ]);

        $teacherStaff = Staff::create([
            'user_id' => $teacherUser->id,
            'employee_code' => 'EMP-002',
            'first_name' => 'Marcus',
            'last_name' => 'Holloway',
            'designation' => 'Senior Mathematics Teacher',
            'phone' => '+91 99999 00003',
            'email' => 'teacher@school.test',
            'joining_date' => '2022-07-15',
            'status' => 'active',
        ]);

        $accountantStaff = Staff::create([
            'user_id' => $accountantUser->id,
            'employee_code' => 'EMP-003',
            'first_name' => 'Sarah',
            'last_name' => 'Connor',
            'designation' => 'Senior Bursar & Accountant',
            'phone' => '+91 99999 00004',
            'email' => 'accountant@school.test',
            'joining_date' => '2021-02-10',
            'status' => 'active',
        ]);

        // 7. Sections
        $sections = [];
        foreach ($classes as $num => $cl) {
            $secA = Section::create([
                'school_class_id' => $cl->id,
                'academic_year_id' => $currentYear->id,
                'name' => 'A',
                'capacity' => 40,
                'class_teacher_id' => ($num === 5) ? $teacherStaff->id : null,
            ]);
            $secB = Section::create([
                'school_class_id' => $cl->id,
                'academic_year_id' => $currentYear->id,
                'name' => 'B',
                'capacity' => 40,
            ]);
            $sections[$num] = ['A' => $secA, 'B' => $secB];
        }

        // Teaching Assignment for Teacher in Class 5-A
        $class5A = $sections[5]['A'];
        TeachingAssignment::create([
            'section_id' => $class5A->id,
            'subject_id' => $subjects['MAT']->id,
            'staff_id' => $teacherStaff->id,
            'academic_year_id' => $currentYear->id,
        ]);
        TeachingAssignment::create([
            'section_id' => $class5A->id,
            'subject_id' => $subjects['SCI']->id,
            'staff_id' => $teacherStaff->id,
            'academic_year_id' => $currentYear->id,
        ]);

        // 8. Guardian & Students
        $guardian = Guardian::create([
            'user_id' => $parentUser->id,
            'first_name' => 'Rajesh',
            'last_name' => 'Sharma',
            'relation' => 'Father',
            'phone' => '+91 98765 11111',
            'email' => 'parent@school.test',
            'occupation' => 'Software Architect',
            'annual_income' => '1800000.00',
            'address' => 'Flat 402, Lotus Towers, Bangalore',
        ]);

        $primaryStudent = Student::create([
            'user_id' => $studentUser->id,
            'admission_no' => 'ADM-2026-0001',
            'first_name' => 'Aarav',
            'last_name' => 'Sharma',
            'gender' => 'male',
            'date_of_birth' => '2016-08-14',
            'blood_group' => 'O+',
            'address' => 'Flat 402, Lotus Towers, Bangalore',
            'city' => 'Bangalore',
            'state' => 'Karnataka',
            'status' => 'active',
        ]);
        $primaryStudent->guardians()->attach($guardian->id, ['is_primary' => true]);

        $primaryEnrollment = Enrollment::create([
            'student_id' => $primaryStudent->id,
            'academic_year_id' => $currentYear->id,
            'section_id' => $class5A->id,
            'roll_number' => 1,
            'status' => 'active',
            'enrolled_on' => '2026-04-01',
        ]);

        // Seed 14 more peer students in Class 5-A
        $peerStudents = [
            ['Ananya', 'Iyer', 'female', '2016-03-12'],
            ['Rohan', 'Verma', 'male', '2016-05-24'],
            ['Diya', 'Patel', 'female', '2016-07-19'],
            ['Ishaan', 'Nair', 'male', '2016-09-05'],
            ['Sanya', 'Malhotra', 'female', '2016-11-28'],
            ['Kabir', 'Mehta', 'male', '2016-02-14'],
            ['Pooja', 'Hegde', 'female', '2016-04-30'],
            ['Aditya', 'Joshi', 'male', '2016-10-10'],
            ['Meera', 'Rao', 'female', '2016-12-01'],
            ['Arjun', 'Kapoor', 'male', '2016-01-18'],
            ['Sneha', 'Reddy', 'female', '2016-06-22'],
            ['Vivaan', 'Gupta', 'male', '2016-08-09'],
            ['Riya', 'Sen', 'female', '2016-10-03'],
            ['Kunal', 'Bansal', 'male', '2016-04-15'],
        ];

        $allClass5AEnrollments = collect([$primaryEnrollment]);

        foreach ($peerStudents as $idx => $peer) {
            $st = Student::create([
                'admission_no' => 'ADM-2026-'.str_pad((string) ($idx + 2), 4, '0', STR_PAD_LEFT),
                'first_name' => $peer[0],
                'last_name' => $peer[1],
                'gender' => $peer[2],
                'date_of_birth' => $peer[3],
                'status' => 'active',
            ]);
            $enr = Enrollment::create([
                'student_id' => $st->id,
                'academic_year_id' => $currentYear->id,
                'section_id' => $class5A->id,
                'roll_number' => $idx + 2,
                'status' => 'active',
                'enrolled_on' => '2026-04-01',
            ]);
            $allClass5AEnrollments->push($enr);
        }

        // 9. Calendar and Attendance
        SchoolCalendarDay::create([
            'date' => '2026-08-15',
            'type' => 'holiday',
            'title' => 'Independence Day',
        ]);
        SchoolCalendarDay::create([
            'date' => '2026-10-02',
            'type' => 'holiday',
            'title' => 'Gandhi Jayanti',
        ]);

        // Mark today's attendance for Class 5-A
        $attendanceData = [];
        foreach ($allClass5AEnrollments as $idx => $enr) {
            // First student Aarav is present, one late, one absent
            if ($idx === 3) {
                $attendanceData[$enr->id] = 'absent';
            } elseif ($idx === 5) {
                $attendanceData[$enr->id] = 'late';
            } else {
                $attendanceData[$enr->id] = 'present';
            }
        }

        $markAction = new MarkAttendance;
        $schoolToday = WorkingCalendar::schoolToday()->toDateString();
        $markAction->execute($class5A, $schoolToday, $attendanceData, $teacherUser);

        // 10. Grade Scale
        $gradeScale = GradeScale::create([
            'name' => 'CBSE Standard 9-Point Scale',
            'is_default' => true,
            'description' => 'Official grading scale for primary and middle school',
        ]);

        Grade::create(['grade_scale_id' => $gradeScale->id, 'name' => 'A1', 'min_percentage' => 91, 'max_percentage' => 100, 'grade_point' => 10.0]);
        Grade::create(['grade_scale_id' => $gradeScale->id, 'name' => 'A2', 'min_percentage' => 81, 'max_percentage' => 90, 'grade_point' => 9.0]);
        Grade::create(['grade_scale_id' => $gradeScale->id, 'name' => 'B1', 'min_percentage' => 71, 'max_percentage' => 80, 'grade_point' => 8.0]);
        Grade::create(['grade_scale_id' => $gradeScale->id, 'name' => 'B2', 'min_percentage' => 61, 'max_percentage' => 70, 'grade_point' => 7.0]);
        Grade::create(['grade_scale_id' => $gradeScale->id, 'name' => 'C1', 'min_percentage' => 51, 'max_percentage' => 60, 'grade_point' => 6.0]);
        Grade::create(['grade_scale_id' => $gradeScale->id, 'name' => 'C2', 'min_percentage' => 41, 'max_percentage' => 50, 'grade_point' => 5.0]);
        Grade::create(['grade_scale_id' => $gradeScale->id, 'name' => 'D',  'min_percentage' => 33, 'max_percentage' => 40, 'grade_point' => 4.0]);
        Grade::create(['grade_scale_id' => $gradeScale->id, 'name' => 'E',  'min_percentage' => 0,  'max_percentage' => 32, 'grade_point' => 0.0]);

        // 11. Exams & Results
        // Exam 1: Term 1 (Published)
        $term1Exam = Exam::create([
            'name' => 'Term 1 Mid-Year Examination',
            'term' => 'Term 1',
            'academic_year_id' => $currentYear->id,
            'start_date' => '2026-09-10',
            'end_date' => '2026-09-20',
            'status' => 'draft',
            'weightage' => 40.00,
            'pass_percentage' => 33.00,
            'grade_scale_id' => $gradeScale->id,
        ]);

        $examSubjects = [];
        foreach ($subjects as $sb) {
            $examSubjects[$sb->code] = ExamSubject::create([
                'exam_id' => $term1Exam->id,
                'school_class_id' => $classes[5]->id,
                'subject_id' => $sb->id,
                'max_marks' => 100.00,
                'pass_marks' => 33.00,
            ]);
        }

        (new OpenMarksEntry)->execute($term1Exam);

        // Enter Marks for all 15 students
        $enterMarksAction = new EnterMarks;
        foreach ($allClass5AEnrollments as $idx => $enr) {
            foreach ($examSubjects as $code => $es) {
                $baseMark = 75 + (($idx * 3) % 20);
                if ($idx === 0) {
                    $baseMark = ($code === 'MAT') ? 96 : 89;
                }
                $enterMarksAction->execute($es, $enr->id, $baseMark, false, 0, null, 'Good comprehension', $teacherUser);
            }
        }

        (new LockExam)->execute($term1Exam, $principalUser);
        (new ComputeResults)->execute($term1Exam);
        (new PublishResults)->execute($term1Exam);

        // Exam 2: Term 2 (Currently in Marks Open state)
        $term2Exam = Exam::create([
            'name' => 'Term 2 Final Examination',
            'term' => 'Term 2',
            'academic_year_id' => $currentYear->id,
            'start_date' => '2027-02-15',
            'end_date' => '2027-02-28',
            'status' => 'draft',
            'weightage' => 60.00,
            'pass_percentage' => 33.00,
            'grade_scale_id' => $gradeScale->id,
        ]);

        foreach ($subjects as $sb) {
            ExamSubject::create([
                'exam_id' => $term2Exam->id,
                'school_class_id' => $classes[5]->id,
                'subject_id' => $sb->id,
                'max_marks' => 100.00,
                'pass_marks' => 33.00,
            ]);
        }
        (new OpenMarksEntry)->execute($term2Exam);

        // 12. Fee Heads, Structures & Invoices
        $tuitionHead = FeeHead::create(['name' => 'Tuition Fee', 'code' => 'TUI', 'is_refundable' => false]);
        $labHead = FeeHead::create(['name' => 'Computer Lab & Tech Fee', 'code' => 'LAB', 'is_refundable' => false]);
        $examHead = FeeHead::create(['name' => 'Examination Fee', 'code' => 'EXAM', 'is_refundable' => false]);

        FeeStructure::create([
            'fee_head_id' => $tuitionHead->id,
            'school_class_id' => $classes[5]->id,
            'academic_year_id' => $currentYear->id,
            'amount' => '4500.00',
            'frequency' => 'monthly',
        ]);
        FeeStructure::create([
            'fee_head_id' => $labHead->id,
            'school_class_id' => $classes[5]->id,
            'academic_year_id' => $currentYear->id,
            'amount' => '800.00',
            'frequency' => 'monthly',
        ]);
        FeeStructure::create([
            'fee_head_id' => $examHead->id,
            'school_class_id' => $classes[5]->id,
            'academic_year_id' => $currentYear->id,
            'amount' => '1200.00',
            'frequency' => 'quarterly',
        ]);

        // Generate Invoices for 2026-04
        $invoiceGen = new GenerateInvoices;
        $invoiceGen->execute('2026-04', '2026-04-20', $currentYear->id);

        // Collect payment for Aarav Sharma's invoice
        $aaravInvoice = Invoice::where('enrollment_id', $primaryEnrollment->id)->first();
        if ($aaravInvoice) {
            (new CollectPayment)->execute(
                $aaravInvoice,
                $aaravInvoice->net_total,
                'upi',
                $accountantUser,
                'UPI-TXN-9823412',
                'Annual fee paid via GooglePay by father Rajesh Sharma'
            );
        }

        // Generate Invoices for 2026-05 (leave some open/pending)
        $invoiceGen->execute('2026-05', '2026-05-20', $currentYear->id);

        // 13. Notices & Board
        $noticeAction = new PublishNotice;
        $noticeAction->execute(
            [
                'title' => 'Welcome to Academic Year 2026-27',
                'content' => "Dear Parents, Guardians and Students,\r\n\r\nWe warmly welcome you to the new academic year 2026-27 at Greenwood International School. Classes commence at 8:30 AM daily.\r\n\r\nWarm regards,\r\nPrincipal Vance",
                'priority' => 'important',
                'is_pinned' => true,
            ],
            [['type' => 'all']],
            $principalUser
        );

        $noticeAction->execute(
            [
                'title' => 'Term 1 Report Cards Released Online',
                'content' => "Report cards for Term 1 Mid-Year Examinations have been computed and published. Parents can log into the student portal to review the grades and download PDF report cards.\r\n\r\nExamination Cell",
                'priority' => 'normal',
            ],
            [['type' => 'role', 'role' => 'Parent'], ['type' => 'role', 'role' => 'Student']],
            $principalUser
        );

        $school->recalculateAndSaveCharge();
    }
}
