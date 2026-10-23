@extends('layouts.app')

@section('title', $student->full_name)
@section('page-title', 'Student Profile: ' . $student->full_name)

@section('content')
<div class="space-y-6">

    <!-- Top Return Bar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('students.index') }}" class="text-xs text-slate-500 hover:text-indigo-600 font-medium flex items-center gap-1">
            ← Back to Student Directory
        </a>
    </div>

    <!-- Student Header Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 rounded-2xl bg-indigo-600 text-white font-extrabold text-2xl flex items-center justify-center shadow-lg shadow-indigo-600/20">
                    {{ substr($student->first_name, 0, 1) }}
                </div>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h2 class="text-xl font-bold text-slate-900">{{ $student->full_name }}</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold
                            {{ $student->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                            {{ ucfirst($student->status) }}
                        </span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1 flex items-center gap-3 flex-wrap">
                        <span>Admission No: <strong class="text-slate-700 font-mono">{{ $student->admission_no }}</strong></span>
                        <span>•</span>
                        <span>Class: <strong class="text-slate-700">{{ $enrollment?->section?->full_name ?? 'Not Assigned' }}</strong></span>
                        <span>•</span>
                        <span>Roll No: <strong class="text-slate-700">{{ $enrollment?->roll_number ?? 'N/A' }}</strong></span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 text-xs bg-slate-50 p-3 rounded-xl border border-slate-200">
                <div class="text-center px-3">
                    <div class="text-[10px] text-slate-400 uppercase font-bold">Attendance</div>
                    <div class="text-base font-bold text-emerald-600 mt-0.5">{{ $attendanceStats['pct'] }}%</div>
                </div>
                <div class="w-px h-8 bg-slate-200"></div>
                <div class="text-center px-3">
                    <div class="text-[10px] text-slate-400 uppercase font-bold">Fee Status</div>
                    <div class="text-base font-bold text-slate-800 mt-0.5">₹{{ number_format($invoices->sum('paid_total')) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Detail Sections -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column: Personal & Guardian Info -->
        <div class="space-y-6 lg:col-span-1">
            <!-- Personal Info -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 text-xs">
                <h3 class="font-bold text-slate-900 text-sm mb-4 border-b border-slate-100 pb-2">Student Demographics</h3>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Gender:</dt>
                        <dd class="font-medium text-slate-800 capitalize">{{ $student->gender }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Date of Birth:</dt>
                        <dd class="font-medium text-slate-800">{{ $student->date_of_birth->format('d M Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Blood Group:</dt>
                        <dd class="font-medium text-slate-800">{{ $student->blood_group ?? 'Not Recorded' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Enrolled On:</dt>
                        <dd class="font-medium text-slate-800">{{ $enrollment?->enrolled_on?->format('d M Y') ?? 'N/A' }}</dd>
                    </div>
                    <div class="pt-2 border-t border-slate-100">
                        <dt class="text-slate-500 mb-1">Address:</dt>
                        <dd class="font-medium text-slate-800 leading-relaxed">{{ $student->address ?? 'Not provided' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Guardian Info -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 text-xs">
                <h3 class="font-bold text-slate-900 text-sm mb-4 border-b border-slate-100 pb-2">Guardian Contact</h3>
                @forelse($student->guardians as $guard)
                <div class="space-y-2.5">
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Name:</dt>
                        <dd class="font-bold text-slate-800">{{ $guard->full_name }} ({{ $guard->relation }})</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Mobile Phone:</dt>
                        <dd class="font-mono font-medium text-indigo-600">{{ $guard->phone }}</dd>
                    </div>
                    @if($guard->email)
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Email:</dt>
                        <dd class="font-medium text-slate-800">{{ $guard->email }}</dd>
                    </div>
                    @endif
                    @if($guard->occupation)
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Occupation:</dt>
                        <dd class="font-medium text-slate-800">{{ $guard->occupation }}</dd>
                    </div>
                    @endif
                </div>
                @empty
                <p class="text-slate-400">No guardian registered.</p>
                @endforelse
            </div>

            <!-- Audit & Record Tracking Metadata -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 text-[11px] text-slate-500 space-y-1.5">
                <div class="font-bold text-slate-700 uppercase tracking-wider text-[10px] mb-2 border-b border-slate-100 pb-1">Audit Record Tracking</div>
                <div class="flex justify-between">
                    <span>Created By:</span>
                    <strong class="text-slate-800">{{ $student->creator->name ?? 'System / Seeder' }}</strong>
                </div>
                <div class="flex justify-between">
                    <span>Created On:</span>
                    <span class="font-mono">{{ $student->created_at->format('d M Y, h:i A') }}</span>
                </div>
                <div class="flex justify-between pt-1 border-t border-slate-100">
                    <span>Last Updated By:</span>
                    <strong class="text-slate-800">{{ $student->updater->name ?? ($student->creator->name ?? 'System') }}</strong>
                </div>
                <div class="flex justify-between">
                    <span>Updated On:</span>
                    <span class="font-mono">{{ $student->updated_at->format('d M Y, h:i A') }}</span>
                </div>
            </div>
        </div>

        <!-- Right Column: Academic Records, Attendance, Exams, Fees -->
        <div class="space-y-6 lg:col-span-2">

            <!-- Examination Results & Report Cards -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-slate-900 text-sm">Academic Results & Report Cards</h3>
                </div>
                <div class="divide-y divide-slate-100 text-xs">
                    @forelse($results as $res)
                    <div class="py-3.5 flex items-center justify-between flex-wrap gap-2">
                        <div>
                            <div class="font-bold text-sm text-slate-900">{{ $res->exam->name }}</div>
                            <div class="text-slate-500 mt-0.5">
                                Marks: <span class="font-semibold text-slate-700">{{ $res->total_marks }}/{{ $res->max_marks }}</span> |
                                Percentage: <span class="font-semibold text-indigo-600">{{ $res->percentage }}%</span> |
                                Grade: <span class="font-bold text-emerald-600">{{ $res->grade }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('exams.report-card', [$res->exam, $enrollment]) }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-2.5 py-1.5 rounded-lg transition text-xs">
                                Preview
                            </a>
                            <a href="{{ route('exams.report-card', [$res->exam, $enrollment, 'download' => '1']) }}" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold px-3 py-1.5 rounded-lg border border-indigo-200 transition text-xs flex items-center gap-1" title="Download Official Marksheet PDF">
                                <span>⬇</span> Marksheet PDF
                            </a>
                        </div>
                    </div>
                    @empty
                    <p class="text-slate-400 text-xs py-3">No exam results recorded for this academic year.</p>
                    @endforelse
                </div>
            </div>

            <!-- Attendance Overview -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-slate-900 text-sm">Recent Daily Attendance</h3>
                    <span class="text-xs text-slate-500">Overall Rate: <strong>{{ $attendanceStats['pct'] }}%</strong></span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs mb-4">
                    <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-100 text-emerald-800">
                        <div class="font-bold text-base">{{ $attendanceStats['present'] }}</div>
                        <div class="text-[11px] text-emerald-600 font-medium">Days Present</div>
                    </div>
                    <div class="p-3 bg-rose-50 rounded-xl border border-rose-100 text-rose-800">
                        <div class="font-bold text-base">{{ $attendanceStats['absent'] }}</div>
                        <div class="text-[11px] text-rose-600 font-medium">Days Absent</div>
                    </div>
                    <div class="p-3 bg-amber-50 rounded-xl border border-amber-100 text-amber-800">
                        <div class="font-bold text-base">{{ $attendanceStats['late'] }}</div>
                        <div class="text-[11px] text-amber-600 font-medium">Late Arrivals</div>
                    </div>
                    <div class="p-3 bg-blue-50 rounded-xl border border-blue-100 text-blue-800">
                        <div class="font-bold text-base">{{ $attendanceStats['leave'] }}</div>
                        <div class="text-[11px] text-blue-600 font-medium">Approved Leave</div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-1.5 pt-2">
                    @foreach($attendances as $att)
                    <span title="{{ $att->date->format('d M Y') }}: {{ ucfirst($att->status) }}"
                        class="w-6 h-6 rounded flex items-center justify-center text-[10px] font-bold cursor-help
                        {{ $att->status === 'present' ? 'bg-emerald-100 text-emerald-700' : '' }}
                        {{ $att->status === 'absent' ? 'bg-rose-100 text-rose-700' : '' }}
                        {{ $att->status === 'late' ? 'bg-amber-100 text-amber-700' : '' }}
                        {{ $att->status === 'leave' ? 'bg-blue-100 text-blue-700' : '' }}">
                        {{ substr(strtoupper($att->status), 0, 1) }}
                    </span>
                    @endforeach
                </div>
            </div>

            <!-- Invoices and Fees -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-slate-900 text-sm">Fee Invoices & Payments</h3>
                </div>
                <div class="divide-y divide-slate-100 text-xs">
                    @forelse($invoices as $inv)
                    <div class="py-3 flex items-center justify-between flex-wrap gap-2">
                        <div>
                            <div class="font-bold text-slate-900 font-mono">{{ $inv->invoice_number }}</div>
                            <div class="text-slate-500 mt-0.5">
                                Period: {{ $inv->period_key }} | Due: {{ $inv->due_on->format('d M Y') }} | Net: ₹{{ number_format($inv->net_total) }}
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold
                                {{ $inv->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ ucfirst($inv->status) }}
                            </span>
                            @if($inv->status !== 'paid')
                            <a href="{{ route('fees.collect', $inv) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">
                                Collect →
                            </a>
                            @elseif($inv->payments->first())
                            <a href="{{ route('fees.receipt', $inv->payments->first()) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">
                                Receipt →
                            </a>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p class="text-slate-400 text-xs py-3">No invoices generated for this student.</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
