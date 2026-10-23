<?php

namespace App\Http\Controllers;

use App\Actions\Students\AdmitStudent;
use App\Actions\Students\PromoteEnrollment;
use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use App\Support\AttendancePercentage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        // If user is a student, take them directly to their own student profile
        if ($user->hasRole('Student')) {
            $student = $user->student ?? Student::where('user_id', $user->id)->first();
            if ($student) {
                return redirect()->route('students.show', $student);
            }
        }

        $query = Student::visibleTo($user)->with(['currentEnrollment.section.schoolClass']);

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                    ->orWhere('last_name', 'like', "%{$s}%")
                    ->orWhere('admission_no', 'like', "%{$s}%");
            });
        }

        if ($request->filled('section_id')) {
            $query->whereHas('currentEnrollment', function ($q) use ($request) {
                $q->where('section_id', $request->input('section_id'));
            });
        }

        $students = $query->paginate(15)->withQueryString();
        $sections = Section::where('academic_year_id', AcademicYear::current()?->id)
            ->with('schoolClass')
            ->get();

        return view('students.index', compact('students', 'sections'));
    }

    public function create(): View
    {
        if (! Auth::user()->hasAnyRole(['Super Admin', 'School Admin', 'Teacher'])) {
            abort(403, 'Unauthorized. Student admissions are restricted to authorized school staff.');
        }

        $classes = SchoolClass::with(['sections' => function ($q) {
            $q->where('academic_year_id', AcademicYear::current()?->id);
        }])->get();

        return view('students.create', compact('classes'));
    }

    public function store(Request $request, AdmitStudent $admitStudent): RedirectResponse
    {
        if (! Auth::user()->hasAnyRole(['Super Admin', 'School Admin', 'Teacher'])) {
            abort(403, 'Unauthorized. Student admissions are restricted to authorized school staff.');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:80',
            'last_name' => 'required|string|max:80',
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'required|date',
            'blood_group' => 'nullable|string|max:10',
            'section_id' => 'required|exists:sections,id',
            'admission_no' => 'nullable|string|max:30|unique:students,admission_no',
            'roll_number' => 'nullable|integer',
            'address' => 'nullable|string',
            'guardian_name' => 'required|string|max:80',
            'guardian_relation' => 'required|string|max:30',
            'guardian_phone' => 'required|string|max:25',
            'guardian_email' => 'nullable|email',
        ]);

        $studentData = [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'gender' => $validated['gender'],
            'date_of_birth' => $validated['date_of_birth'],
            'blood_group' => $validated['blood_group'] ?? null,
            'admission_no' => $validated['admission_no'] ?? null,
            'roll_number' => $validated['roll_number'] ?? null,
            'address' => $validated['address'] ?? null,
        ];

        $guardianParts = explode(' ', $validated['guardian_name'], 2);
        $guardianData = [
            'first_name' => $guardianParts[0],
            'last_name' => $guardianParts[1] ?? '',
            'relation' => $validated['guardian_relation'],
            'phone' => $validated['guardian_phone'],
            'email' => $validated['guardian_email'] ?? null,
        ];

        $student = $admitStudent->execute($studentData, $guardianData, (int) $validated['section_id']);

        return redirect()->route('students.show', $student)->with('success', "Student {$student->full_name} admitted successfully.");
    }

    public function show(Student $student): View
    {
        /** @var User $user */
        $user = Auth::user();

        // Enforce visibleTo scope
        $allowed = Student::visibleTo($user)->whereKey($student->id)->exists();
        if (! $allowed) {
            abort(403, 'Unauthorized access to student record.');
        }

        $student->load(['guardians', 'documents', 'currentEnrollment.section.schoolClass']);
        $enrollment = $student->currentEnrollment;

        $attendances = $enrollment ? $enrollment->attendances()->latest('date')->take(30)->get() : collect();
        $attendanceStats = [
            'present' => $attendances->where('status', 'present')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'leave' => $attendances->where('status', 'leave')->count(),
        ];
        $attendanceStats['pct'] = AttendancePercentage::calculate(
            $attendanceStats['present'],
            $attendanceStats['late'],
            $attendanceStats['absent'],
            $attendanceStats['leave']
        );

        $results = $enrollment ? $enrollment->results()->with('exam')->get() : collect();
        $invoices = $enrollment ? $enrollment->invoices()->with('payments')->latest()->get() : collect();

        return view('students.show', compact('student', 'enrollment', 'attendances', 'attendanceStats', 'results', 'invoices'));
    }

    public function promoteView(): View
    {
        if (! Auth::user()->hasAnyRole(['Super Admin', 'School Admin', 'Teacher'])) {
            abort(403, 'Unauthorized. Student promotions are restricted to authorized school staff.');
        }

        $years = AcademicYear::latest()->get();
        $currentYear = AcademicYear::current();
        $classes = SchoolClass::with('sections')->get();

        return view('students.promote', compact('years', 'currentYear', 'classes'));
    }

    public function promoteSubmit(Request $request, PromoteEnrollment $promoter): RedirectResponse
    {
        if (! Auth::user()->hasAnyRole(['Super Admin', 'School Admin', 'Teacher'])) {
            abort(403, 'Unauthorized. Student promotions are restricted to authorized school staff.');
        }

        $data = $request->validate([
            'from_section_id' => 'required|exists:sections,id',
            'to_academic_year_id' => 'required|exists:academic_years,id',
            'to_section_id' => 'required|exists:sections,id',
        ]);

        $enrollments = Enrollment::where('section_id', $data['from_section_id'])
            ->where('status', 'active')
            ->get();

        $promoted = 0;
        foreach ($enrollments as $enr) {
            $promoter->execute($enr, (int) $data['to_academic_year_id'], (int) $data['to_section_id'], 'promoted');
            $promoted++;
        }

        return redirect()->route('students.index')->with('success', "Promoted {$promoted} students successfully.");
    }
}
