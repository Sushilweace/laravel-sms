@extends('layouts.app')

@section('title', $school->name . ' - School Profile & Child Entities')

@section('content')
<div class="space-y-6">

    <!-- Back Navigation & Top Actions -->
    <div class="flex items-center justify-between no-print">
        <a href="{{ route('admin.schools.index') }}" class="text-xs text-slate-500 hover:text-indigo-600 font-medium flex items-center gap-1">
            ← Back to Schools Directory
        </a>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.schools.edit', $school) }}" class="text-xs font-semibold px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition">
                ✏ Edit School Profile
            </a>
            <a href="{{ route('settings.school') }}" class="text-xs font-semibold px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl border border-indigo-200 transition">
                ⚙ Configure Plan & Modules
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center gap-2">
            <span>✓</span> {{ session('success') }}
        </div>
    @endif

    <!-- School Header Profile Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-slate-200">
            <div class="flex items-start gap-4">
                <div class="w-16 h-16 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-black text-2xl shadow-xl shadow-indigo-600/30">
                    {{ substr($school->name, 0, 1) }}
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ $school->name }}</h1>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-200 font-mono">
                            {{ $school->code }}
                        </span>
                        @if($school->parent)
                            <a href="{{ route('admin.schools.show', $school->parent) }}" class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100 transition">
                                ↳ Branch of {{ $school->parent->name }}
                            </a>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                🏢 Root School / Trust
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-1 max-w-2xl">
                        {{ $school->address ?? 'Campus address not specified.' }}
                    </p>
                    <div class="flex items-center gap-4 text-xs text-slate-500 mt-2 flex-wrap">
                        <span>📞 {{ $school->phone ?? 'No phone' }}</span>
                        <span>✉ {{ $school->email ?? 'No email' }}</span>
                        <span>🌐 {{ $school->timezone }} ({{ $school->currency }})</span>
                    </div>
                </div>
            </div>

            <!-- Subscription Tier Badge Card -->
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-right min-w-[200px]">
                <div class="text-[10px] text-slate-400 uppercase tracking-wider font-bold">Subscription Plan</div>
                <div class="text-lg font-black text-indigo-600 uppercase mt-0.5">{{ $school->package_tier }} Tier</div>
                <div class="text-xs font-mono text-slate-700 mt-1">
                    ₹ {{ number_format($school->package_charge, 2) }} / {{ $school->billing_cycle }}
                </div>
            </div>
        </div>

        <!-- Metric Summary Bar -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 pt-6 text-center text-xs">
            <a href="{{ route('admin.schools.show', [$school, 'tab' => 'students']) }}" 
               class="p-3 rounded-xl border transition {{ $tab === 'students' ? 'border-indigo-600 bg-indigo-50/50 shadow-sm' : 'border-slate-200 bg-slate-50 hover:bg-slate-100' }}">
                <span class="text-slate-500 block text-[11px] font-semibold uppercase">Enrolled Students</span>
                <strong class="text-2xl font-black text-slate-900 mt-0.5 block">{{ $stats['students'] }}</strong>
            </a>

            <a href="{{ route('admin.schools.show', [$school, 'tab' => 'teachers']) }}" 
               class="p-3 rounded-xl border transition {{ $tab === 'teachers' ? 'border-indigo-600 bg-indigo-50/50 shadow-sm' : 'border-slate-200 bg-slate-50 hover:bg-slate-100' }}">
                <span class="text-slate-500 block text-[11px] font-semibold uppercase">Teaching Faculty</span>
                <strong class="text-2xl font-black text-indigo-600 mt-0.5 block">{{ $stats['teachers'] }}</strong>
            </a>

            <a href="{{ route('admin.schools.show', [$school, 'tab' => 'accountants']) }}" 
               class="p-3 rounded-xl border transition {{ $tab === 'accountants' ? 'border-indigo-600 bg-indigo-50/50 shadow-sm' : 'border-slate-200 bg-slate-50 hover:bg-slate-100' }}">
                <span class="text-slate-500 block text-[11px] font-semibold uppercase">Finance / Accountants</span>
                <strong class="text-2xl font-black text-emerald-600 mt-0.5 block">{{ $stats['accountants'] }}</strong>
            </a>

            <a href="{{ route('admin.schools.show', [$school, 'tab' => 'parents']) }}" 
               class="p-3 rounded-xl border transition {{ $tab === 'parents' ? 'border-indigo-600 bg-indigo-50/50 shadow-sm' : 'border-slate-200 bg-slate-50 hover:bg-slate-100' }}">
                <span class="text-slate-500 block text-[11px] font-semibold uppercase">Parents / Guardians</span>
                <strong class="text-2xl font-black text-blue-600 mt-0.5 block">{{ $stats['parents'] }}</strong>
            </a>

            <a href="{{ route('admin.schools.show', [$school, 'tab' => 'branches']) }}" 
               class="p-3 rounded-xl border transition {{ $tab === 'branches' ? 'border-indigo-600 bg-indigo-50/50 shadow-sm' : 'border-slate-200 bg-slate-50 hover:bg-slate-100' }} col-span-2 sm:col-span-1">
                <span class="text-slate-500 block text-[11px] font-semibold uppercase">Child Campuses</span>
                <strong class="text-2xl font-black text-amber-600 mt-0.5 block">{{ $stats['branches'] }}</strong>
            </a>
        </div>
    </div>

    <!-- Tabbed Navigation -->
    <div class="flex items-center gap-2 border-b border-slate-200 text-xs font-semibold overflow-x-auto pb-1">
        <a href="{{ route('admin.schools.show', [$school, 'tab' => 'students']) }}"
           class="px-4 py-2.5 rounded-t-xl transition border-b-2 flex items-center gap-1.5 whitespace-nowrap {{ $tab === 'students' ? 'border-indigo-600 text-indigo-600 bg-white shadow-sm' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
            <span>🎓</span> Students & Linked Parents ({{ $stats['students'] }})
        </a>

        <a href="{{ route('admin.schools.show', [$school, 'tab' => 'teachers']) }}"
           class="px-4 py-2.5 rounded-t-xl transition border-b-2 flex items-center gap-1.5 whitespace-nowrap {{ $tab === 'teachers' ? 'border-indigo-600 text-indigo-600 bg-white shadow-sm' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
            <span>👩‍🏫</span> Teaching Faculty ({{ $stats['teachers'] }})
        </a>

        <a href="{{ route('admin.schools.show', [$school, 'tab' => 'accountants']) }}"
           class="px-4 py-2.5 rounded-t-xl transition border-b-2 flex items-center gap-1.5 whitespace-nowrap {{ $tab === 'accountants' ? 'border-indigo-600 text-indigo-600 bg-white shadow-sm' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
            <span>💳</span> Accountants & Finance ({{ $stats['accountants'] }})
        </a>

        <a href="{{ route('admin.schools.show', [$school, 'tab' => 'parents']) }}"
           class="px-4 py-2.5 rounded-t-xl transition border-b-2 flex items-center gap-1.5 whitespace-nowrap {{ $tab === 'parents' ? 'border-indigo-600 text-indigo-600 bg-white shadow-sm' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
            <span>👨‍👩‍👧</span> Parent Directory ({{ $stats['parents'] }})
        </a>

        <a href="{{ route('admin.schools.show', [$school, 'tab' => 'branches']) }}"
           class="px-4 py-2.5 rounded-t-xl transition border-b-2 flex items-center gap-1.5 whitespace-nowrap {{ $tab === 'branches' ? 'border-indigo-600 text-indigo-600 bg-white shadow-sm' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
            <span>🏫</span> Child Branch Campuses ({{ $stats['branches'] }})
        </a>
    </div>

    <!-- Tab 1: Students & Their Linked Parents -->
    @if($tab === 'students')
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                <h3 class="font-bold text-slate-900 text-sm">Enrolled Students & Linked Parent Records</h3>
                <span class="text-xs text-slate-500">Showing {{ $students->count() }} of {{ $students->total() }} students</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4 w-28">Admission No</th>
                            <th class="py-3 px-4 min-w-[150px]">Student Full Name</th>
                            <th class="py-3 px-4 w-32">Class & Section</th>
                            <th class="py-3 px-4 min-w-[200px]">Linked Parent / Guardian (parent_id)</th>
                            <th class="py-3 px-4 w-24">Status</th>
                            <th class="py-3 px-4 text-right w-24">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($students as $st)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                    {{ $st->admission_no }}
                                </td>

                                <td class="py-3 px-4 font-bold text-slate-900">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-[10px]">
                                            {{ substr($st->first_name, 0, 1) }}{{ substr($st->last_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('students.show', $st) }}" class="hover:text-indigo-600 transition">
                                                {{ $st->full_name }}
                                            </a>
                                            <div class="text-[10px] text-slate-400 capitalize">{{ $st->gender }} • DOB: {{ $st->date_of_birth->format('d M Y') }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-3 px-4 text-slate-700 font-medium">
                                    {{ $st->enrollments->first()?->section?->full_name ?? 'Not Enrolled' }}
                                </td>

                                <td class="py-3 px-4">
                                    @forelse($st->guardians as $g)
                                        <div class="flex items-center gap-1.5 font-semibold text-slate-800">
                                            <span>👤 {{ $g->full_name }}</span>
                                            <span class="text-[10px] px-1.5 py-0.2 rounded bg-indigo-50 text-indigo-700 font-normal">({{ $g->relation }})</span>
                                        </div>
                                        <div class="text-[10px] font-mono text-slate-500">📞 {{ $g->phone }}</div>
                                    @empty
                                        <span class="text-slate-400 italic">No parent linked</span>
                                    @endforelse
                                </td>

                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                        {{ $st->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                        {{ $st->status }}
                                    </span>
                                </td>

                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('students.show', $st) }}" class="text-xs font-semibold px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg transition">
                                        View Profile →
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    No students registered under this school.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($students->hasPages())
                <div class="p-4 border-t border-slate-200">
                    {{ $students->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- Tab 2: Teaching Faculty -->
    @if($tab === 'teachers')
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                <h3 class="font-bold text-slate-900 text-sm">Teaching Faculty & Assigned Classes</h3>
                <span class="text-xs text-slate-500">{{ $teachers->count() }} Teachers</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4 w-28">Emp Code</th>
                            <th class="py-3 px-4 min-w-[160px]">Faculty Member</th>
                            <th class="py-3 px-4">Designation</th>
                            <th class="py-3 px-4">Contact Details</th>
                            <th class="py-3 px-4 min-w-[200px]">Assigned Sections & Subjects</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($teachers as $tch)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ $tch->employee_code }}</td>

                                <td class="py-3 px-4 font-bold text-slate-900">
                                    <a href="{{ route('staff.show', $tch) }}" class="hover:text-indigo-600 transition">
                                        {{ $tch->first_name }} {{ $tch->last_name }}
                                    </a>
                                    <div class="text-[10px] text-slate-400 capitalize">{{ $tch->gender }} • {{ $tch->qualification ?? 'Faculty' }}</div>
                                </td>

                                <td class="py-3 px-4 text-slate-700 font-semibold">
                                    {{ $tch->designation }}
                                </td>

                                <td class="py-3 px-4 text-slate-600 font-mono">
                                    <div>{{ $tch->phone }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $tch->email }}</div>
                                </td>

                                <td class="py-3 px-4">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($tch->teachingAssignments as $ta)
                                            <span class="px-2 py-0.5 rounded bg-indigo-50 border border-indigo-200 text-indigo-700 text-[10px] font-semibold">
                                                {{ $ta->section->name }}: {{ $ta->subject->code ?? $ta->subject->name }}
                                            </span>
                                        @empty
                                            <span class="text-slate-400 italic">No section assignments</span>
                                        @endforelse
                                    </div>
                                </td>

                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('staff.show', $tch) }}" class="text-xs font-semibold px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition">
                                        Profile →
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    No teaching faculty assigned to this school.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Tab 3: Accountants & Finance Staff -->
    @if($tab === 'accountants')
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                <h3 class="font-bold text-slate-900 text-sm">Accountants, Bursars & Financial Staff</h3>
                <span class="text-xs text-slate-500">{{ $accountants->count() }} Staff Members</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4 w-28">Emp Code</th>
                            <th class="py-3 px-4 min-w-[160px]">Accountant Name</th>
                            <th class="py-3 px-4">Designation</th>
                            <th class="py-3 px-4">Contact</th>
                            <th class="py-3 px-4">Portal Account</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($accountants as $acc)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ $acc->employee_code }}</td>

                                <td class="py-3 px-4 font-bold text-slate-900">
                                    <a href="{{ route('staff.show', $acc) }}" class="hover:text-indigo-600 transition">
                                        {{ $acc->first_name }} {{ $acc->last_name }}
                                    </a>
                                    <div class="text-[10px] text-slate-400 font-mono">Joined: {{ $acc->joining_date?->format('d M Y') ?? '—' }}</div>
                                </td>

                                <td class="py-3 px-4 font-semibold text-slate-700">
                                    {{ $acc->designation }}
                                </td>

                                <td class="py-3 px-4 font-mono text-slate-600">
                                    <div>{{ $acc->phone }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $acc->email }}</div>
                                </td>

                                <td class="py-3 px-4">
                                    @if($acc->user)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            Active Login ({{ $acc->user->email }})
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic">No linked login</span>
                                    @endif
                                </td>

                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('staff.show', $acc) }}" class="text-xs font-semibold px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition">
                                        Profile →
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    No finance/accountant staff registered under this school.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Tab 4: Parent Directory & Their Children -->
    @if($tab === 'parents')
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Parents & Guardians with Enrolled Children (parent_id)</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Family units associated with students in this campus.</p>
                </div>
                <span class="text-xs text-slate-500">{{ $guardians->total() }} Parents</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4 min-w-[160px]">Parent / Guardian Name</th>
                            <th class="py-3 px-4 w-28">Relation</th>
                            <th class="py-3 px-4 min-w-[160px]">Contact Info</th>
                            <th class="py-3 px-4">Occupation</th>
                            <th class="py-3 px-4 min-w-[240px]">Enrolled Child Students (Students Linked)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($guardians as $guard)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4 font-bold text-slate-900">
                                    {{ $guard->full_name }}
                                </td>

                                <td class="py-3 px-4 font-semibold text-slate-700">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-[10px]">
                                        {{ $guard->relation }}
                                    </span>
                                </td>

                                <td class="py-3 px-4 font-mono text-slate-600">
                                    <div>📞 {{ $guard->phone }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $guard->email ?? 'No email' }}</div>
                                </td>

                                <td class="py-3 px-4 text-slate-600">
                                    {{ $guard->occupation ?? '—' }}
                                </td>

                                <td class="py-3 px-4">
                                    <div class="space-y-1">
                                        @forelse($guard->students as $child)
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('students.show', $child) }}" class="font-bold text-indigo-600 hover:text-indigo-800 transition">
                                                    🎓 {{ $child->full_name }}
                                                </a>
                                                <span class="text-[10px] font-mono text-slate-500">({{ $child->admission_no }})</span>
                                                <span class="text-[10px] text-slate-400">• {{ $child->enrollments->first()?->section?->full_name ?? 'Class' }}</span>
                                            </div>
                                        @empty
                                            <span class="text-slate-400 italic">No active students</span>
                                        @endforelse
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-slate-400">
                                    No parent/guardian records linked to this school.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($guardians->hasPages())
                <div class="p-4 border-t border-slate-200">
                    {{ $guardians->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- Tab 5: Child Branch Campuses -->
    @if($tab === 'branches')
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Child Branch Campuses (linked to parent_id: {{ $school->id }})</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Subsidiary schools and satellite campuses under {{ $school->name }}.</p>
                </div>
                <a href="{{ route('admin.schools.create', ['parent_id' => $school->id]) }}" class="text-xs font-semibold px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white transition">
                    ➕ Add Branch School
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Branch School Name</th>
                            <th class="py-3 px-4">Code</th>
                            <th class="py-3 px-4">Students</th>
                            <th class="py-3 px-4">Staff</th>
                            <th class="py-3 px-4">Package</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($childBranches as $branch)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4 font-bold text-slate-900">
                                    <a href="{{ route('admin.schools.show', $branch) }}" class="hover:text-indigo-600 transition flex items-center gap-1.5">
                                        <span>🏫</span> {{ $branch->name }}
                                    </a>
                                    <div class="text-[10px] text-slate-400">{{ $branch->address ?? 'Branch Campus' }}</div>
                                </td>

                                <td class="py-3 px-4 font-mono font-bold text-slate-700">
                                    {{ $branch->code }}
                                </td>

                                <td class="py-3 px-4 font-bold text-slate-800">
                                    👥 {{ $branch->students_count }} Students
                                </td>

                                <td class="py-3 px-4 font-bold text-slate-800">
                                    👩‍🏫 {{ $branch->staff_count }} Staff
                                </td>

                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-indigo-50 text-indigo-700">
                                        {{ $branch->package_tier }}
                                    </span>
                                </td>

                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('admin.schools.show', $branch) }}" class="text-xs font-semibold px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg transition">
                                        Manage Campus →
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    No child branch campuses registered under this school.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>
@endsection
