<?php

namespace App\Http\Controllers;

use App\Actions\Academic\LockAcademicYear;
use App\Actions\Academic\SetCurrentAcademicYear;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Staff;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicController extends Controller
{
    public function index(): View
    {
        $years = AcademicYear::latest()->get();
        $currentYear = AcademicYear::current();
        $classes = SchoolClass::with(['sections' => function ($q) use ($currentYear) {
            $q->where('academic_year_id', $currentYear?->id)->with('classTeacher');
        }, 'subjects'])->orderBy('numeric_level')->get();
        $subjects = Subject::all();
        $staffMembers = Staff::where('status', 'active')->get();

        return view('academic.index', compact('years', 'currentYear', 'classes', 'subjects', 'staffMembers'));
    }

    public function storeYear(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:20|unique:academic_years,name',
            'starts_on' => 'required|date',
            'ends_on' => 'required|date|after:starts_on',
        ]);

        $year = AcademicYear::create($data);

        if ($request->boolean('is_current')) {
            (new SetCurrentAcademicYear)->execute($year);
        }

        return back()->with('success', "Academic Year {$year->name} created successfully.");
    }

    public function setCurrentYear(AcademicYear $year): RedirectResponse
    {
        (new SetCurrentAcademicYear)->execute($year);

        return back()->with('success', "Active academic year set to {$year->name}.");
    }

    public function lockYear(AcademicYear $year): RedirectResponse
    {
        (new LockAcademicYear)->execute($year);

        return back()->with('success', "Academic year {$year->name} is now locked.");
    }

    public function storeClass(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:50|unique:school_classes,name',
            'numeric_level' => 'required|integer|min:1|max:20',
            'next_class_id' => 'nullable|exists:school_classes,id',
        ]);

        SchoolClass::create($data);

        return back()->with('success', "Class {$data['name']} added.");
    }

    public function storeSection(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'school_class_id' => 'required|exists:school_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'name' => 'required|string|max:20',
            'capacity' => 'nullable|integer|min:1',
            'class_teacher_id' => 'nullable|exists:staff,id',
        ]);

        Section::create($data);

        return back()->with('success', "Section {$data['name']} added.");
    }

    public function storeSubject(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:subjects,code',
            'type' => 'required|in:theory,practical,both',
        ]);

        Subject::create($data);

        return back()->with('success', "Subject {$data['name']} added.");
    }

    public function assignTeaching(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'staff_id' => 'required|exists:staff,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        TeachingAssignment::updateOrCreate(
            [
                'section_id' => $data['section_id'],
                'subject_id' => $data['subject_id'],
            ],
            $data
        );

        return back()->with('success', 'Teacher assigned successfully.');
    }
}
