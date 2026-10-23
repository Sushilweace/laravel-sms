<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Models\School;
use App\Models\Staff;
use App\Models\Student;
use App\Support\PackagePricing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchoolManagementController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->input('q', ''));

        $query = School::with('parent')
            ->withCount(['students', 'staff', 'children as branches_count'])
            ->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $schools = $query->paginate(15)->withQueryString();

        $totalSchools = School::count();
        $totalStudents = Student::count();
        $totalStaff = Staff::count();

        return view('admin.schools.index', compact(
            'schools',
            'search',
            'totalSchools',
            'totalStudents',
            'totalStaff'
        ));
    }

    public function create(): View
    {
        $parentSchools = School::whereNull('parent_id')->get();
        $allTiers = PackagePricing::allTiers();

        return view('admin.schools.create', compact('parentSchools', 'allTiers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:20|unique:schools,code',
            'parent_id' => 'nullable|exists:schools,id',
            'phone' => 'nullable|string|max:25',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
            'timezone' => 'required|string|max:50',
            'currency' => 'required|string|size:3',
            'package_tier' => 'required|string|in:starter,standard,premium,enterprise,custom',
            'billing_cycle' => 'required|string|in:monthly,annually',
        ]);

        $tier = $data['package_tier'];
        $cycle = $data['billing_cycle'];
        $tiersCatalog = PackagePricing::allTiers();
        $tierFeatures = $tiersCatalog[$tier]['features'] ?? array_keys(PackagePricing::allFeatures());
        $activeFeatures = array_fill_keys($tierFeatures, true);

        $quote = PackagePricing::calculateCharge($tier, $activeFeatures, 0, $cycle);

        $school = School::create([
            'parent_id' => $data['parent_id'] ?: null,
            'name' => $data['name'],
            'code' => strtoupper($data['code']),
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'timezone' => $data['timezone'],
            'currency' => strtoupper($data['currency']),
            'package_tier' => $tier,
            'billing_cycle' => $cycle,
            'package_charge' => $quote['final_charge'],
            'features' => $activeFeatures,
            'pricing_config' => $quote,
        ]);

        return redirect()->route('admin.schools.show', $school)
            ->with('success', "School '{$school->name}' created successfully with {$school->package_tier} tier.");
    }

    public function show(School $school, Request $request): View
    {
        $school->load(['parent', 'children']);

        $tab = $request->input('tab', 'students');

        // 1. Students belonging to this school
        $students = Student::where('school_id', $school->id)
            ->orWhere(function ($q) use ($school) {
                // If school_id was null, show students of default primary school
                if ($school->id === School::first()?->id) {
                    $q->whereNull('school_id');
                }
            })
            ->with(['guardians', 'enrollments' => fn ($e) => $e->where('status', 'active')->with('section.schoolClass')])
            ->latest()
            ->paginate(20, ['*'], 'students_page')
            ->withQueryString();

        // 2. Teachers / Faculty
        $teachers = Staff::where('school_id', $school->id)
            ->orWhere(function ($q) use ($school) {
                if ($school->id === School::first()?->id) {
                    $q->whereNull('school_id');
                }
            })
            ->where(function ($q) {
                $q->where('designation', 'like', '%teacher%')
                    ->orWhereHas('user.roles', fn ($r) => $r->where('name', 'Teacher'));
            })
            ->with(['user.roles', 'teachingAssignments.section', 'teachingAssignments.subject'])
            ->get();

        // 3. Accountants / Bursar
        $accountants = Staff::where('school_id', $school->id)
            ->orWhere(function ($q) use ($school) {
                if ($school->id === School::first()?->id) {
                    $q->whereNull('school_id');
                }
            })
            ->where(function ($q) {
                $q->where('designation', 'like', '%accountant%')
                    ->orWhere('designation', 'like', '%bursar%')
                    ->orWhereHas('user.roles', fn ($r) => $r->where('name', 'Accountant'));
            })
            ->with('user')
            ->get();

        // 4. Parents / Guardians related to students of this school
        $studentIds = Student::where('school_id', $school->id)
            ->orWhere(function ($q) use ($school) {
                if ($school->id === School::first()?->id) {
                    $q->whereNull('school_id');
                }
            })
            ->pluck('id');

        $guardians = Guardian::whereHas('students', fn ($q) => $q->whereIn('students.id', $studentIds))
            ->with(['students.enrollments' => fn ($e) => $e->where('status', 'active')->with('section.schoolClass')])
            ->paginate(20, ['*'], 'parents_page')
            ->withQueryString();

        // 5. Child Branch Schools (if any)
        $childBranches = $school->children()->withCount(['students', 'staff'])->get();

        // Stats
        $studentCount = Student::where('school_id', $school->id)
            ->orWhere(function ($q) use ($school) {
                if ($school->id === School::first()?->id) {
                    $q->whereNull('school_id');
                }
            })->count();

        $stats = [
            'students' => $studentCount,
            'teachers' => $teachers->count(),
            'accountants' => $accountants->count(),
            'parents' => Guardian::whereHas('students', fn ($q) => $q->whereIn('students.id', $studentIds))->count(),
            'branches' => $childBranches->count(),
        ];

        return view('admin.schools.show', compact(
            'school',
            'tab',
            'students',
            'teachers',
            'accountants',
            'guardians',
            'childBranches',
            'stats'
        ));
    }

    public function edit(School $school): View
    {
        $parentSchools = School::where('id', '!=', $school->id)->get();
        $allTiers = PackagePricing::allTiers();

        return view('admin.schools.edit', compact('school', 'parentSchools', 'allTiers'));
    }

    public function update(Request $request, School $school): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:20|unique:schools,code,'.$school->id,
            'parent_id' => 'nullable|exists:schools,id|different:id',
            'phone' => 'nullable|string|max:25',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
            'timezone' => 'required|string|max:50',
            'currency' => 'required|string|size:3',
            'package_tier' => 'required|string|in:starter,standard,premium,enterprise,custom',
            'billing_cycle' => 'required|string|in:monthly,annually',
        ]);

        $tier = $data['package_tier'];
        $cycle = $data['billing_cycle'];
        $activeFeatures = $school->features ?? [];

        $studentCount = $school->students()->count();
        $quote = PackagePricing::calculateCharge($tier, $activeFeatures, $studentCount, $cycle);

        $school->update([
            'parent_id' => $data['parent_id'] ?: null,
            'name' => $data['name'],
            'code' => strtoupper($data['code']),
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'timezone' => $data['timezone'],
            'currency' => strtoupper($data['currency']),
            'package_tier' => $tier,
            'billing_cycle' => $cycle,
            'package_charge' => $quote['final_charge'],
            'pricing_config' => $quote,
        ]);

        return redirect()->route('admin.schools.show', $school)
            ->with('success', "School '{$school->name}' updated successfully.");
    }

    public function destroy(School $school): RedirectResponse
    {
        // Prevent deleting the primary school if it's the only one
        if (School::count() <= 1) {
            return back()->with('error', 'Cannot delete the primary root school.');
        }

        $name = $school->name;

        // If this school has child branches, unbind them
        $school->children()->update(['parent_id' => null]);

        $school->delete();

        return redirect()->route('admin.schools.index')
            ->with('success', "School '{$name}' was deleted successfully.");
    }

    public function switchActive(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'school_id' => 'required|exists:schools,id',
        ]);

        $school = School::findOrFail($data['school_id']);
        session(['active_school_id' => $school->id]);

        return back()->with('success', "Active campus context switched to: {$school->name} ({$school->code})");
    }
}
