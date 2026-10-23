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
use App\Models\Mark;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function index(): View
    {
        $currentYear = AcademicYear::current();
        $exams = Exam::where('academic_year_id', $currentYear?->id)
            ->with(['academicYear', 'gradeScale'])
            ->latest()
            ->get();

        return view('exams.index', compact('exams', 'currentYear'));
    }

    public function create(): View
    {
        $currentYear = AcademicYear::current();
        $gradeScales = GradeScale::all();
        $classes = SchoolClass::with('subjects')->get();

        return view('exams.create', compact('currentYear', 'gradeScales', 'classes'));
    }

    public function store(Request $request): RedirectResponse
    {
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
        $sections = Section::where('school_class_id', $examSubject->school_class_id)
            ->where('academic_year_id', $exam->academic_year_id)
            ->get();

        $selectedSectionId = $request->input('section_id', $sections->first()?->id);

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

        $school = School::first();

        $data = compact('exam', 'enrollment', 'result', 'marks', 'school');

        if ($request->boolean('download')) {
            $pdf = Pdf::loadView('exams.report-card-pdf', $data);

            return $pdf->download("Report-Card-{$enrollment->student->admission_no}-{$exam->name}.pdf");
        }

        return view('exams.report-card', $data);
    }
}
