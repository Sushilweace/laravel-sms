<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\AttendanceDay;
use App\Models\Exam;
use App\Models\Invoice;
use App\Models\Notice;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Staff;
use App\Models\Student;
use App\Models\User;
use App\Support\AttendancePercentage;
use App\Support\WorkingCalendar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $currentYear = AcademicYear::current();
        $todayStr = WorkingCalendar::schoolToday()->toDateString();

        $data = [
            'user' => $user,
            'currentYear' => $currentYear,
            'todayStr' => $todayStr,
        ];

        if ($user->hasRole(['Super Admin', 'School Admin', 'Accountant'])) {
            $data['totalStudents'] = Student::active()->count();
            $data['totalStaff'] = Staff::where('status', 'active')->count();
            $data['totalClasses'] = SchoolClass::count();
            $data['totalSections'] = Section::where('academic_year_id', $currentYear?->id)->count();

            // Today's attendance
            $todayAttendances = Attendance::where('date', $todayStr)->get();
            $data['presentToday'] = $todayAttendances->where('status', 'present')->count();
            $data['absentToday'] = $todayAttendances->where('status', 'absent')->count();
            $data['lateToday'] = $todayAttendances->where('status', 'late')->count();
            $data['leaveToday'] = $todayAttendances->where('status', 'leave')->count();
            $data['todayAttendancePct'] = AttendancePercentage::calculate(
                $data['presentToday'],
                $data['lateToday'],
                $data['absentToday'],
                $data['leaveToday']
            );

            // Fees
            $data['totalInvoiced'] = Invoice::where('academic_year_id', $currentYear?->id)->sum('total_amount');
            $data['totalCollected'] = Payment::where('status', 'completed')->sum('amount');
            $data['totalPending'] = max(0, $data['totalInvoiced'] - $data['totalCollected']);

            // Recent Notices
            $data['notices'] = Notice::visibleTo($user)->latest()->take(5)->get();

            // Recent Exams
            $data['recentExams'] = Exam::where('academic_year_id', $currentYear?->id)->latest()->take(4)->get();
        } elseif ($user->hasRole('Teacher')) {
            $staff = $user->staff;
            $data['staff'] = $staff;
            $data['assignedSections'] = $staff ? $staff->teachingAssignments()->with(['section.schoolClass', 'subject'])->get() : collect();
            $data['classTeacherSection'] = $staff ? $staff->classTeacherSections()->where('academic_year_id', $currentYear?->id)->first() : null;

            if ($data['classTeacherSection']) {
                $data['isAttendanceMarkedToday'] = AttendanceDay::where('section_id', $data['classTeacherSection']->id)
                    ->where('date', $todayStr)
                    ->exists();
            } else {
                $data['isAttendanceMarkedToday'] = false;
            }

            $data['notices'] = Notice::visibleTo($user)->latest()->take(5)->get();
            $data['openExams'] = Exam::where('status', 'marks_open')->latest()->get();
        } else {
            // Parent or Student
            $student = $user->hasRole('Student')
                ? $user->student
                : $user->guardian?->students()->first();

            $data['student'] = $student;
            $data['enrollment'] = $student?->currentEnrollment;

            if ($data['enrollment']) {
                $attendances = Attendance::where('enrollment_id', $data['enrollment']->id)->get();
                $data['studentAttendancePct'] = AttendancePercentage::calculate(
                    $attendances->where('status', 'present')->count(),
                    $attendances->where('status', 'late')->count(),
                    $attendances->where('status', 'absent')->count(),
                    $attendances->where('status', 'leave')->count()
                );
                $data['results'] = $data['enrollment']->results()->with('exam')->get();
                $data['invoices'] = $data['enrollment']->invoices()->latest()->take(5)->get();
            } else {
                $data['studentAttendancePct'] = 0;
                $data['results'] = collect();
                $data['invoices'] = collect();
            }

            $data['notices'] = Notice::visibleTo($user)->latest()->take(5)->get();
        }

        return view('dashboard', $data);
    }
}
