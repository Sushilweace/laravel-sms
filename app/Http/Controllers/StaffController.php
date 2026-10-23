<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(Request $request): View
    {
        $query = Staff::with('user');

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                    ->orWhere('last_name', 'like', "%{$s}%")
                    ->orWhere('employee_code', 'like', "%{$s}%")
                    ->orWhere('designation', 'like', "%{$s}%");
            });
        }

        $staff = $query->paginate(15)->withQueryString();

        return view('staff.index', compact('staff'));
    }

    public function create(): View
    {
        return view('staff.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:80',
            'last_name' => 'required|string|max:80',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:25',
            'designation' => 'required|string|max:80',
            'qualification' => 'nullable|string|max:120',
            'gender' => 'required|in:male,female,other',
            'joining_date' => 'nullable|date',
            'role' => 'required|in:Teacher,School Admin,Accountant',
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => "{$data['first_name']} {$data['last_name']}",
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'user_type' => 'staff',
                'password' => Hash::make('password123'),
            ]);
            $user->assignRole($data['role']);

            $codeCount = Staff::count() + 1;
            $empCode = 'EMP-'.str_pad((string) $codeCount, 3, '0', STR_PAD_LEFT);

            Staff::create([
                'user_id' => $user->id,
                'employee_code' => $empCode,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'gender' => $data['gender'],
                'qualification' => $data['qualification'] ?? null,
                'designation' => $data['designation'],
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'],
                'joining_date' => $data['joining_date'] ?? now()->toDateString(),
                'status' => 'active',
            ]);
        });

        return redirect()->route('staff.index')->with('success', 'Staff member created successfully with default password: password123');
    }

    public function show(Staff $staff): View
    {
        $staff->load(['teachingAssignments.section.schoolClass', 'teachingAssignments.subject', 'classTeacherSections']);

        return view('staff.show', compact('staff'));
    }
}
