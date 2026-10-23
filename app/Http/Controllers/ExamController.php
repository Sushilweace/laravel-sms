<?php

namespace App\Http\Controllers;

use App\Actions\Exams\ComputeResults;
use App\Actions\Exams\EnterMarks;
use App\Actions\Exams\LockExam;
use App\Actions\Exams\OpenMarksEntry;
use App\Actions\Exams\PublishResults;
use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\GradeScale;
use App\Models\Guardian;
use App\Models\Mark;
use App\Models\Result;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExamController extends Controller
{
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $currentYear = AcademicYear::current();

        // 1. If user is Student or Parent, show their personal exams & published report cards
        if ($user->hasRole(['Student', 'Parent'])) {
            $student = null;
            if ($user->hasRole('Student')) {
                $student = $user->student ?? Student::where('user_id', $user->id)->first();
            } elseif ($user->hasRole('Parent')) {
                $guardian = $user->guardian ?? Guardian::where('user_id', $user->id)->first();
                $student = $guardian?->students()->first();
            }

            $enrollment = $student?->currentEnrollment;
            $results = $enrollment
                ? Result::where('enrollment_id', $enrollment->id)->with(['exam.academicYear', 'exam.gradeScale'])->get()
                : collect();

            $publishedExams = Exam::where('academic_year_id', $currentYear?->id)
                ->where('status', 'published')
                ->latest()
                ->get();

            return view('exams.student', compact('student', 'enrollment', 'results', 'publishedExams', 'currentYear'));
        }

        $exams = Exam::where('academic_year_id', $currentYear?->id)
            ->with(['academicYear', 'gradeScale'])
            ->latest()
            ->get();

        return view('exams.index', compact('exams', 'currentYear'));
    }

    public function create(): View
    {
        if (! Auth::user()->hasAnyRole(['Super Admin', 'School Admin', 'Teacher'])) {
            abort(403, 'Unauthorized. Exam management is restricted to academic staff.');
        }

        $currentYear = AcademicYear::current();
        $gradeScales = GradeScale::all();
        $classes = SchoolClass::with('subjects')->get();

        return view('exams.create', compact('currentYear', 'gradeScales', 'classes'));
    }

    public function store(Request $request): RedirectResponse
    {
        if (! Auth::user()->hasAnyRole(['Super Admin', 'School Admin', 'Teacher'])) {
            abort(403, 'Unauthorized. Exam management is restricted to academic staff.');
        }
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'term' => 'nullable|string|max:40',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'pass_percentage' => 'required|numeric|min:1|max:100',
            'weightage' => 'nullable|numeric|min:0|max:100',
            'grade_scale_id' => 'required|exists:grade_scales,id',
            'class_ids' => 'required|array',
            'class_ids.*' => 'exists:school_classes,id',
        ]);

        $exam = Exam::create([
            'name' => $data['name'],
            'term' => $data['term'] ?? null,
            'academic_year_id' => AcademicYear::current()?->id,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'pass_percentage' => $data['pass_percentage'],
            'weightage' => $data['weightage'] ?? null,
            'grade_scale_id' => $data['grade_scale_id'],
            'status' => 'draft',
        ]);

        // Create ExamSubjects for each chosen class and its subjects
        foreach ($data['class_ids'] as $classId) {
            $class = SchoolClass::find($classId);
            foreach ($class->subjects as $subject) {
                ExamSubject::create([
                    'exam_id' => $exam->id,
                    'school_class_id' => $class->id,
                    'subject_id' => $subject->id,
                    'max_marks' => 100.00,
                    'pass_marks' => $data['pass_percentage'],
                ]);
            }
        }

        return redirect()->route('exams.show', $exam)->with('success', "Exam {$exam->name} created successfully with default subjects.");
    }

    public function show(Exam $exam): View
    {
        $exam->load(['examSubjects.schoolClass', 'examSubjects.subject', 'gradeScale.grades', 'results.enrollment.student']);
        $results = $exam->results()->with(['enrollment.student', 'enrollment.section.schoolClass'])->orderBy('class_rank')->get();

        return view('exams.show', compact('exam', 'results'));
    }

    public function openMarks(Exam $exam, OpenMarksEntry $action): RedirectResponse
    {
        try {
            $action->execute($exam);

            return back()->with('success', 'Exam marks entry is now open.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function lockExam(Exam $exam, LockExam $action): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        try {
            $action->execute($exam, $user);

            return back()->with('success', 'Exam has been locked for marks entry.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function compute(Exam $exam, ComputeResults $action): RedirectResponse
    {
        try {
            $action->execute($exam);

            return back()->with('success', 'Results, percentages and ranks computed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function publish(Exam $exam, PublishResults $action): RedirectResponse
    {
        try {
            $action->execute($exam);

            return back()->with('success', 'Exam results are now published and visible to parents and students.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function enterMarksView(Exam $exam, ExamSubject $examSubject, Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();

        $sectionsQuery = Section::where('school_class_id', $examSubject->school_class_id)
            ->where('academic_year_id', $exam->academic_year_id);

        if ($user->hasRole('Teacher') && $user->staff) {
            $sectionsQuery->whereIn('id', $user->staff->visibleSectionIds());
        }

        $sections = $sectionsQuery->get();

        if ($user->hasRole('Teacher') && $sections->isEmpty()) {
            abort(403, 'Unauthorized. You are not assigned to any section for this class or subject.');
        }

        $selectedSectionId = $request->input('section_id', $sections->first()?->id);
        if ($selectedSectionId && $user->hasRole('Teacher') && $user->staff) {
            if (! $user->staff->visibleSectionIds()->contains((int) $selectedSectionId)) {
                abort(403, 'Unauthorized. You cannot enter marks for an unassigned class section.');
            }
        }

        $enrollments = Enrollment::where('section_id', $selectedSectionId)
            ->where('status', 'active')
            ->with('student')
            ->orderBy('roll_number')
            ->get();

        $marks = Mark::where('exam_subject_id', $examSubject->id)
            ->whereIn('enrollment_id', $enrollments->pluck('id'))
            ->get()
            ->keyBy('enrollment_id');

        return view('exams.enter-marks', compact('exam', 'examSubject', 'sections', 'selectedSectionId', 'enrollments', 'marks'));
    }

    public function saveMarks(Exam $exam, ExamSubject $examSubject, Request $request, EnterMarks $action): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $data = $request->validate([
            'marks' => 'required|array',
            'absent' => 'nullable|array',
            'remarks' => 'nullable|array',
            'change_reason' => 'nullable|string|max:255',
        ]);

        if ($user->hasRole('Teacher') && $user->staff) {
            $allowedEnrollments = Enrollment::whereIn('section_id', $user->staff->visibleSectionIds())->pluck('id')->toArray();
            foreach (array_keys($data['marks']) as $enrId) {
                if (! in_array((int) $enrId, $allowedEnrollments)) {
                    abort(403, 'Unauthorized. You can only enter marks for students in your assigned sections.');
                }
            }
        }

        try {
            foreach ($data['marks'] as $enrollmentId => $score) {
                $isAbsent = ! empty($data['absent'][$enrollmentId]);
                $marksObtained = ($score !== null && $score !== '') ? (float) $score : null;
                $rem = $data['remarks'][$enrollmentId] ?? null;

                $action->execute(
                    $examSubject,
                    (int) $enrollmentId,
                    $marksObtained,
                    $isAbsent,
                    0.0,
                    null,
                    $rem,
                    $user,
                    $data['change_reason'] ?? null
                );
            }

            return back()->with('success', 'Marks saved successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function reportCard(Exam $exam, Enrollment $enrollment, Request $request): View|Response
    {
        /** @var User $user */
        $user = Auth::user();

        // Non-faculty users (students, parents) can only download report cards of their own enrollment
        if (! $user->hasAnyRole(['Super Admin', 'School Admin', 'Teacher'])) {
            $isOwn = Enrollment::visibleTo($user)->where('id', $enrollment->id)->exists();
            if (! $isOwn) {
                abort(403, 'Unauthorized access to student progress report card.');
            }
        }

        $enrollment->load(['student', 'section.schoolClass', 'academicYear']);
        $exam->load(['gradeScale.grades']);

        $result = $exam->results()->where('enrollment_id', $enrollment->id)->first();
        if (! $result) {
            abort(404, 'Result not found or not computed yet.');
        }

        $marks = Mark::whereHas('examSubject', fn ($q) => $q->where('exam_id', $exam->id))
            ->where('enrollment_id', $enrollment->id)
            ->with(['examSubject.subject'])
            ->get();

        $school = School::current() ?? School::first();

        $data = compact('exam', 'enrollment', 'result', 'marks', 'school');

        if ($request->boolean('download')) {
            $pdf = Pdf::loadView('exams.report-card-pdf', $data);

            return $pdf->download("Marksheet-{$enrollment->student->admission_no}-{$exam->name}.pdf");
        }

        return view('exams.report-card', $data);
    }

    public function classMarksheet(Exam $exam, Request $request): View|Response|StreamedResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->hasAnyRole(['Super Admin', 'School Admin', 'Teacher'])) {
            abort(403, 'Unauthorized. Consolidated class marksheets can only be accessed by teachers and administrators.');
        }

        $classIds = $exam->examSubjects()->pluck('school_class_id')->unique();
        $sectionsQuery = Section::whereIn('school_class_id', $classIds)
            ->where('academic_year_id', $exam->academic_year_id)
            ->with('schoolClass');

        if ($user->hasRole('Teacher') && $user->staff) {
            $sectionsQuery->whereIn('id', $user->staff->visibleSectionIds());
        }

        $sections = $sectionsQuery->get();

        if ($user->hasRole('Teacher') && $sections->isEmpty()) {
            abort(403, 'Unauthorized. You do not have assigned sections for this examination.');
        }

        if ($request->filled('section_id') && $user->hasRole('Teacher') && $user->staff) {
            if (! $user->staff->visibleSectionIds()->contains((int) $request->input('section_id'))) {
                abort(403, 'Unauthorized access to unassigned section marksheet.');
            }
        }

        $selectedSectionId = $request->input('section_id', $sections->first()?->id);
        $section = $sections->firstWhere('id', $selectedSectionId) ?? $sections->first();

        if (! $section) {
            abort(404, 'No sections found for this examination.');
        }

        $subjects = $exam->examSubjects()
            ->where('school_class_id', $section->school_class_id)
            ->with('subject')
            ->get();

        $enrollments = Enrollment::where('section_id', $section->id)
            ->where('status', 'active')
            ->with('student')
            ->orderBy('roll_number')
            ->get();

        $marks = Mark::whereIn('exam_subject_id', $subjects->pluck('id'))
            ->whereIn('enrollment_id', $enrollments->pluck('id'))
            ->get()
            ->groupBy('enrollment_id');

        $results = $exam->results()
            ->whereIn('enrollment_id', $enrollments->pluck('id'))
            ->get()
            ->keyBy('enrollment_id');

        // Statistics
        $total = $enrollments->count();
        $appeared = 0;
        $passed = 0;
        $failed = 0;
        $percentages = [];

        foreach ($enrollments as $enr) {
            $stM = $marks[$enr->id] ?? collect();
            if ($stM->isNotEmpty()) {
                $appeared++;
            }
            $res = $results[$enr->id] ?? null;
            if ($res) {
                $percentages[] = (float) $res->percentage;
                if ($res->result_status === 'pass') {
                    $passed++;
                } else {
                    $failed++;
                }
            }
        }

        $avgPct = count($percentages) > 0 ? round(array_sum($percentages) / count($percentages), 1) : 0;
        $highPct = count($percentages) > 0 ? max($percentages) : 0;
        $lowPct = count($percentages) > 0 ? min($percentages) : 0;
        $passRate = $appeared > 0 ? round(($passed / $appeared) * 100, 1) : 0;

        $stats = [
            'total' => $total,
            'appeared' => $appeared,
            'passed' => $passed,
            'failed' => $failed,
            'pass_percentage' => $passRate,
            'average_percentage' => $avgPct,
            'highest_percentage' => $highPct,
            'lowest_percentage' => $lowPct,
        ];

        $school = School::current() ?? School::first();

        // CSV Export
        if ($request->input('format') === 'csv') {
            $csvHeaders = array_merge(
                ['Roll No', 'Admission No', 'Student Full Name'],
                $subjects->map(fn ($s) => $s->subject->name.' (Max: '.(int) $s->max_marks.')')->toArray(),
                ['Total Marks', 'Max Marks', 'Percentage', 'Grade', 'GPA', 'Class Rank', 'Section Rank', 'Result Status']
            );

            $rows = [];
            foreach ($enrollments as $enr) {
                $res = $results[$enr->id] ?? null;
                $stM = $marks[$enr->id] ?? collect();

                $row = [
                    $enr->roll_number ?? '',
                    $enr->student->admission_no,
                    $enr->student->full_name,
                ];

                foreach ($subjects as $s) {
                    $m = $stM->firstWhere('exam_subject_id', $s->id);
                    if (! $m) {
                        $row[] = '—';
                    } elseif ($m->is_absent) {
                        $row[] = 'AB';
                    } else {
                        $row[] = (float) $m->final_marks;
                    }
                }

                $row[] = $res ? (float) $res->total_marks : '—';
                $row[] = $res ? (int) $res->max_marks : '—';
                $row[] = $res ? $res->percentage.'%' : '—';
                $row[] = $res?->grade ?? '—';
                $row[] = $res?->gpa ?? '—';
                $row[] = $res?->class_rank ?? '—';
                $row[] = $res?->section_rank ?? '—';
                $row[] = $res ? strtoupper($res->result_status) : '—';

                $rows[] = $row;
            }

            $fileName = "Marksheet-{$exam->name}-{$section->name}.csv";

            return response()->streamDownload(function () use ($csvHeaders, $rows) {
                $out = fopen('php://output', 'w');
                fputcsv($out, $csvHeaders);
                foreach ($rows as $r) {
                    fputcsv($out, $r);
                }
                fclose($out);
            }, $fileName, ['Content-Type' => 'text/csv']);
        }

        // PDF Download
        if ($request->boolean('download') || $request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('exams.class-marksheet-pdf', compact(
                'exam', 'section', 'sections', 'subjects', 'enrollments', 'marks', 'results', 'stats', 'school'
            ))->setPaper('a4', 'landscape');

            return $pdf->download("Consolidated-Marksheet-{$exam->name}-{$section->name}.pdf");
        }

        return view('exams.class-marksheet', compact(
            'exam', 'section', 'sections', 'subjects', 'enrollments', 'marks', 'results', 'stats', 'school'
        ));
    }

    public function downloadSubjectMarksheet(Exam $exam, ExamSubject $examSubject, Request $request): Response|StreamedResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $examSubject->load(['subject', 'schoolClass']);
        $sectionsQuery = Section::where('school_class_id', $examSubject->school_class_id)
            ->where('academic_year_id', $exam->academic_year_id);

        if ($user->hasRole('Teacher') && $user->staff) {
            $sectionsQuery->whereIn('id', $user->staff->visibleSectionIds());
        }

        $sections = $sectionsQuery->get();

        if ($user->hasRole('Teacher') && $sections->isEmpty()) {
            abort(403, 'Unauthorized. You are not assigned to this subject or class section.');
        }

        if ($request->filled('section_id') && $user->hasRole('Teacher') && $user->staff) {
            if (! $user->staff->visibleSectionIds()->contains((int) $request->input('section_id'))) {
                abort(403, 'Unauthorized access to unassigned section subject marksheet.');
            }
        }

        $selectedSectionId = $request->input('section_id', $sections->first()?->id);
        $section = $sections->firstWhere('id', $selectedSectionId) ?? $sections->first();

        $enrollments = Enrollment::where('section_id', $section?->id)
            ->where('status', 'active')
            ->with('student')
            ->orderBy('roll_number')
            ->get();

        $marks = Mark::where('exam_subject_id', $examSubject->id)
            ->whereIn('enrollment_id', $enrollments->pluck('id'))
            ->get()
            ->keyBy('enrollment_id');

        $school = School::current() ?? School::first();

        if ($request->input('format') === 'csv') {
            $headers = ['Roll No', 'Admission No', 'Student Full Name', 'Max Marks', 'Marks Obtained', 'Grace Marks', 'Final Marks', 'Status', 'Remarks'];
            $rows = [];
            foreach ($enrollments as $enr) {
                $m = $marks[$enr->id] ?? null;
                $isPass = $m && ! $m->is_absent && (float) $m->final_marks >= ($examSubject->pass_marks ?? 33);
                $rows[] = [
                    $enr->roll_number ?? '',
                    $enr->student->admission_no,
                    $enr->student->full_name,
                    (int) $examSubject->max_marks,
                    $m ? ($m->is_absent ? 'AB' : (float) $m->marks_obtained) : '—',
                    $m ? (float) $m->grace_marks : '0',
                    $m ? (float) $m->final_marks : '—',
                    $m ? ($m->is_absent ? 'ABSENT' : ($isPass ? 'PASS' : 'FAIL')) : '—',
                    $m->remarks ?? '',
                ];
            }

            return response()->streamDownload(function () use ($headers, $rows) {
                $out = fopen('php://output', 'w');
                fputcsv($out, $headers);
                foreach ($rows as $r) {
                    fputcsv($out, $r);
                }
                fclose($out);
            }, "Subject-Marksheet-{$examSubject->subject->name}-{$exam->name}.csv", ['Content-Type' => 'text/csv']);
        }

        $pdf = Pdf::loadView('exams.subject-marksheet-pdf', compact(
            'exam', 'examSubject', 'section', 'enrollments', 'marks', 'school'
        ))->setPaper('a4', 'portrait');

        return $pdf->download("Subject-Marksheet-{$examSubject->subject->name}-{$exam->name}.pdf");
    }

    public function bulkMarksheetsZip(Exam $exam, Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->hasAnyRole(['Super Admin', 'School Admin', 'Teacher'])) {
            abort(403, 'Unauthorized. Bulk marksheet export is restricted to faculty.');
        }

        $exam->load(['gradeScale.grades']);
        $school = School::current() ?? School::first();

        $classIds = $exam->examSubjects()->pluck('school_class_id')->unique();
        $sectionsQuery = Section::whereIn('school_class_id', $classIds)
            ->where('academic_year_id', $exam->academic_year_id)
            ->with('schoolClass');

        if ($user->hasRole('Teacher') && $user->staff) {
            $sectionsQuery->whereIn('id', $user->staff->visibleSectionIds());
        }

        $sections = $sectionsQuery->get();

        if ($user->hasRole('Teacher') && $sections->isEmpty()) {
            abort(403, 'Unauthorized. You do not have assigned sections for bulk marksheet export.');
        }

        if ($request->filled('section_id') && $user->hasRole('Teacher') && $user->staff) {
            if (! $user->staff->visibleSectionIds()->contains((int) $request->input('section_id'))) {
                abort(403, 'Unauthorized access to bulk marksheets for unassigned section.');
            }
        }

        $selectedSectionId = $request->input('section_id', $sections->first()?->id);
        $section = $sections->firstWhere('id', $selectedSectionId) ?? $sections->first();

        if (! $section) {
            return back()->with('error', 'No sections found for this exam.');
        }

        $enrollments = Enrollment::where('section_id', $section->id)
            ->where('status', 'active')
            ->with(['student', 'section.schoolClass'])
            ->orderBy('roll_number')
            ->get();

        if ($enrollments->isEmpty()) {
            return back()->with('error', 'No active students enrolled in this section.');
        }

        $allResults = $exam->results()
            ->whereIn('enrollment_id', $enrollments->pluck('id'))
            ->get()
            ->keyBy('enrollment_id');

        $allMarks = Mark::whereHas('examSubject', fn ($q) => $q->where('exam_id', $exam->id))
            ->whereIn('enrollment_id', $enrollments->pluck('id'))
            ->with(['examSubject.subject'])
            ->get()
            ->groupBy('enrollment_id');

        // Create temporary zip archive
        $zipFile = tempnam(sys_get_temp_dir(), 'sms_marksheets_');
        $zip = new \ZipArchive;

        if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Could not create ZIP archive for marksheets.');
        }

        $pdfCount = 0;
        foreach ($enrollments as $enrollment) {
            $result = $allResults[$enrollment->id] ?? null;
            if (! $result) {
                continue;
            }

            $marks = $allMarks[$enrollment->id] ?? collect();
            $data = compact('exam', 'enrollment', 'result', 'marks', 'school');

            $pdf = Pdf::loadView('exams.report-card-pdf', $data);
            $roll = str_pad((string) ($enrollment->roll_number ?? 0), 2, '0', STR_PAD_LEFT);
            $cleanName = preg_replace('/[^A-Za-z0-9_-]/', '_', $enrollment->student->full_name);
            $fileName = "Roll_{$roll}_{$enrollment->student->admission_no}_{$cleanName}.pdf";

            $zip->addFromString($fileName, $pdf->output());
            $pdfCount++;
        }

        $zip->close();

        if ($pdfCount === 0) {
            @unlink($zipFile);

            return back()->with('error', 'No computed results found to generate individual marksheets for this section.');
        }

        $downloadFileName = "Marksheets-{$exam->name}-{$section->name}.zip";

        return response()->download($zipFile, $downloadFileName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }
}
