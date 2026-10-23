@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'System Dashboard')

@section('content')
<div class="space-y-6">

    @if($user->hasRole(['Super Admin', 'School Admin', 'Accountant']))
    <!-- Admin / Management KPI Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Students -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Students</p>
                    <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($totalStudents) }}</h3>
                    <p class="text-xs text-emerald-600 font-medium mt-1">Active enrollments</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
            </div>
        </div>

        <!-- Teachers -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Staff & Faculty</p>
                    <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($totalStaff) }}</h3>
                    <p class="text-xs text-indigo-600 font-medium mt-1">{{ $totalClasses }} classes configured</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
            </div>
        </div>

        <!-- Today Attendance -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Today's Attendance</p>
                    <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ $todayAttendancePct }}%</h3>
                    <p class="text-xs text-slate-500 mt-1">{{ $presentToday }} Present / {{ $absentToday }} Absent</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>

        <!-- Fee Collection -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Fees Collected</p>
                    <h3 class="text-2xl font-bold text-slate-900 mt-1">₹{{ number_format($totalCollected) }}</h3>
                    <p class="text-xs text-amber-600 font-medium mt-1">₹{{ number_format($totalPending) }} pending dues</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Management Panels -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Active Exams -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-900 text-sm">Examinations & Results</h3>
                <a href="{{ route('exams.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold">View all →</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentExams as $exam)
                <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition">
                    <div>
                        <div class="font-semibold text-sm text-slate-900">{{ $exam->name }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">Term: {{ $exam->term ?? 'N/A' }} | Pass: {{ $exam->pass_percentage }}%</div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                            {{ $exam->status === 'published' ? 'bg-emerald-100 text-emerald-700' : '' }}
                            {{ $exam->status === 'marks_open' ? 'bg-blue-100 text-blue-700' : '' }}
                            {{ $exam->status === 'locked' ? 'bg-amber-100 text-amber-700' : '' }}
                            {{ $exam->status === 'draft' ? 'bg-slate-100 text-slate-700' : '' }}">
                            {{ strtoupper(str_replace('_', ' ', $exam->status)) }}
                        </span>
                        <a href="{{ route('exams.show', $exam) }}" class="text-xs bg-slate-100 hover:bg-indigo-50 text-slate-700 hover:text-indigo-600 font-medium px-3 py-1.5 rounded-lg border border-slate-200 transition">
                            Manage
                        </a>
                    </div>
                </div>
                @empty
                <div class="p-6 text-center text-sm text-slate-500">No exams created yet.</div>
                @endforelse
            </div>
        </div>

        <!-- School Notice Board -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-900 text-sm">Official Notice Board</h3>
                <a href="{{ route('notices.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold">All notices →</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($notices as $notice)
                <div class="p-4 hover:bg-slate-50 transition">
                    <div class="flex items-center justify-between">
                        <span class="font-semibold text-sm text-slate-900 flex items-center gap-1.5">
                            @if($notice->is_pinned)
                            <span class="text-amber-500">📌</span>
                            @endif
                            {{ $notice->title }}
                        </span>
                        <span class="text-[11px] text-slate-400">{{ $notice->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-xs text-slate-600 mt-1 line-clamp-2 leading-relaxed">{{ $notice->content }}</p>
                </div>
                @empty
                <div class="p-6 text-center text-sm text-slate-500">No announcements posted.</div>
                @endforelse
            </div>
        </div>
    </div>

    @elseif($user->hasRole('Teacher'))
    <!-- Teacher Dashboard -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Class Teacher Role -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm lg:col-span-1">
            <h3 class="font-bold text-slate-900 text-sm mb-4">Class Teacher Responsibilities</h3>
            @if($classTeacherSection)
                <div class="p-4 bg-indigo-50/60 rounded-xl border border-indigo-100">
                    <div class="text-xs text-indigo-600 font-semibold uppercase tracking-wider">Assigned Section</div>
                    <div class="text-xl font-bold text-indigo-950 mt-1">{{ $classTeacherSection->full_name }}</div>
                    <div class="text-xs text-slate-600 mt-2">
                        Status Today:
                        @if($isAttendanceMarkedToday)
                            <span class="text-emerald-700 font-semibold">✓ Attendance Submitted</span>
                        @else
                            <span class="text-amber-700 font-semibold">⚠ Attendance Pending</span>
                        @endif
                    </div>
                    <a href="{{ route('attendance.index', ['section_id' => $classTeacherSection->id]) }}" class="mt-4 inline-flex items-center justify-center w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-xs py-2 px-3 rounded-lg transition shadow-sm">
                        Open Daily Attendance Register →
                    </a>
                </div>
            @else
                <p class="text-xs text-slate-500">You are not designated as Class Teacher for any section this year.</p>
            @endif
        </div>

        <!-- Assigned Teaching Sections -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm lg:col-span-2">
            <h3 class="font-bold text-slate-900 text-sm mb-4">Teaching Assignments</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @forelse($assignedSections as $assign)
                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between">
                    <div>
                        <div class="font-bold text-sm text-slate-900">{{ $assign->subject->name }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">{{ $assign->section->full_name }}</div>
                    </div>
                    <a href="{{ route('attendance.index', ['section_id' => $assign->section_id]) }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold">
                        View Register →
                    </a>
                </div>
                @empty
                <div class="text-xs text-slate-500 col-span-2">No subjects assigned yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    @else
    <!-- Parent / Student Portal -->
    <div class="space-y-6">
        @if($student)
        <!-- Student Header -->
        <div class="bg-gradient-to-r from-indigo-700 to-indigo-900 rounded-2xl p-6 text-white shadow-lg">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-white/10 flex items-center justify-center text-2xl font-bold border border-white/20">
                    {{ substr($student->first_name, 0, 1) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold">{{ $student->full_name }}</h2>
                    <p class="text-indigo-200 text-xs mt-0.5">
                        Admission No: {{ $student->admission_no }} |
                        Class: {{ $enrollment?->section?->full_name ?? 'Enrolled' }} |
                        Roll No: {{ $enrollment?->roll_number ?? 'N/A' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Metrics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                <span class="text-xs font-semibold text-slate-500 uppercase">Attendance Rate</span>
                <div class="text-2xl font-bold text-slate-900 mt-1">{{ $studentAttendancePct }}%</div>
                <div class="w-full bg-slate-100 rounded-full h-2 mt-3 overflow-hidden">
                    <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $studentAttendancePct }}%"></div>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                <span class="text-xs font-semibold text-slate-500 uppercase">Latest Result</span>
                <div class="text-2xl font-bold text-slate-900 mt-1">
                    {{ $results->first()?->grade ?? 'A1' }}
                    <span class="text-sm font-medium text-slate-500">({{ $results->first()?->percentage ?? '92' }}%)</span>
                </div>
                <p class="text-xs text-emerald-600 font-medium mt-1">Class Rank: #{{ $results->first()?->class_rank ?? '1' }}</p>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                <span class="text-xs font-semibold text-slate-500 uppercase">Fee Status</span>
                <div class="text-2xl font-bold text-slate-900 mt-1">
                    ₹{{ number_format($invoices->sum('paid_total')) }} Paid
                </div>
                <p class="text-xs text-slate-500 mt-1">{{ $invoices->where('status', 'paid')->count() }} paid invoices</p>
            </div>
        </div>

        <!-- Results & Report Cards -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <h3 class="font-bold text-slate-900 text-sm mb-4">Exam Results & Report Cards</h3>
            <div class="divide-y divide-slate-100">
                @forelse($results as $res)
                <div class="py-3.5 flex items-center justify-between">
                    <div>
                        <div class="font-semibold text-sm text-slate-900">{{ $res->exam->name }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">Total Marks: {{ $res->total_marks }}/{{ $res->max_marks }} | Percentage: {{ $res->percentage }}% | Grade: {{ $res->grade }}</div>
                    </div>
                    <a href="{{ route('exams.report-card', [$res->exam, $enrollment]) }}" class="inline-flex items-center gap-1.5 text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold px-3 py-1.5 rounded-lg border border-indigo-200 transition">
                        View Report Card PDF →
                    </a>
                </div>
                @empty
                <p class="text-xs text-slate-500">No published examination results available yet.</p>
                @endforelse
            </div>
        </div>
        @else
        <div class="bg-white p-8 rounded-2xl border border-slate-200 text-center">
            <p class="text-slate-600 text-sm">No student profile linked to your account. Please consult the school office.</p>
        </div>
        @endif
    </div>
    @endif

</div>
@endsection
