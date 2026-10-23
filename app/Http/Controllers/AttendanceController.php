<?php

namespace App\Http\Controllers;

use App\Actions\Attendance\MarkAttendance;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\AttendanceDay;
use App\Models\AttendanceSummary;
use App\Models\Enrollment;
use App\Models\Section;
use App\Models\Setting;
use App\Models\User;
use App\Support\WorkingCalendar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $currentYear = AcademicYear::current();

        $sectionsQuery = Section::where('academic_year_id', $currentYear?->id)->with('schoolClass');
        if ($user->hasRole('Teacher') && $user->staff) {
            $allowedSectionIds = $user->staff->visibleSectionIds();
            $sectionsQuery->whereIn('id', $allowedSectionIds);
        }
        $sections = $sectionsQuery->get();

        $selectedSectionId = $request->input('section_id', $sections->first()?->id);
        $selectedDate = $request->input('date', WorkingCalendar::schoolToday()->toDateString());

        $students = collect();
        $existingRecords = collect();
        $isFinalized = false;
        $attendanceDay = null;

        if ($selectedSectionId) {
            $students = Enrollment::where('section_id', $selectedSectionId)
                ->where('status', 'active')
                ->with('student')
                ->orderBy('roll_number')
                ->get();

            $existingRecords = Attendance::whereIn('enrollment_id', $students->pluck('id'))
                ->where('date', $selectedDate)
                ->get()
                ->keyBy('enrollment_id');

            $attendanceDay = AttendanceDay::where('section_id', $selectedSectionId)
                ->where('date', $selectedDate)
                ->first();
            $isFinalized = (bool) $attendanceDay?->finalized_at;
        }

        return view('attendance.index', compact('sections', 'selectedSectionId', 'selectedDate', 'students', 'existingRecords', 'isFinalized', 'attendanceDay'));
    }

    public function save(Request $request, MarkAttendance $marker): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $data = $request->validate([
            'section_id' => 'required|exists:sections,id',
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*' => 'required|in:present,absent,late,leave',
            'edit_reason' => 'nullable|string|max:255',
        ]);

        $section = Section::findOrFail($data['section_id']);

        try {
            $marker->execute($section, $data['date'], $data['attendance'], $user, $data['edit_reason'] ?? null);

            return back()->with('success', "Attendance saved successfully for {$section->full_name} on {$data['date']}.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function reports(Request $request): View
    {
        $currentYear = AcademicYear::current();
        $sections = Section::where('academic_year_id', $currentYear?->id)->with('schoolClass')->get();
        $selectedSectionId = $request->input('section_id', $sections->first()?->id);
        $lowThreshold = (int) Setting::get('attendance.low_threshold', 75);

        $summaries = collect();
        if ($selectedSectionId) {
            $summaries = AttendanceSummary::whereHas('enrollment', fn ($q) => $q->where('section_id', $selectedSectionId))
                ->with('enrollment.student')
                ->latest('month')
                ->paginate(20)
                ->withQueryString();
        }

        return view('attendance.reports', compact('sections', 'selectedSectionId', 'summaries', 'lowThreshold'));
    }
}
