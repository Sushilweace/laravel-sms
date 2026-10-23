@extends('layouts.app')

@section('title', 'My Attendance Record')

@section('content')
<div class="space-y-6 max-w-6xl mx-auto">

    <!-- Header Banner -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-black text-2xl shadow-lg shadow-indigo-600/30">
                {{ substr($student?->first_name ?? auth()->user()->name, 0, 1) }}
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800 uppercase tracking-wider">
                        Student Attendance
                    </span>
                    <span class="text-xs text-slate-500 font-mono">Academic Year: {{ $currentYear?->name ?? '2026-27' }}</span>
                </div>
                <h1 class="text-xl font-extrabold text-slate-900 mt-1">
                    {{ $student?->full_name ?? auth()->user()->name }}
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    Admission No: <strong class="font-mono text-slate-700">{{ $student?->admission_no ?? 'ADM-2026' }}</strong>
                    @if($enrollment)
                        • {{ $enrollment->section?->full_name }} (Roll #{{ $enrollment->roll_number ?? '1' }})
                    @endif
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <span class="text-xs text-slate-500">
                School: <strong class="text-slate-800">{{ $currentSchool->name ?? 'Greenwood High' }}</strong>
            </span>
        </div>
    </div>

    <!-- Attendance Percentage & Status Indicator -->
    @php
        $pct = (float) ($stats['percentage'] ?? 100);
        $isGood = $pct >= 75.0;
    @endphp
    <div class="p-4 rounded-2xl border flex items-center justify-between gap-4 shadow-sm {{ $isGood ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-rose-50 border-rose-200 text-rose-900' }}">
        <div class="flex items-center gap-3">
            <div class="text-2xl">{{ $isGood ? '✓' : '⚠️' }}</div>
            <div>
                <div class="text-sm font-bold">
                    {{ $isGood ? 'Good Standing: Attendance is above mandatory minimum threshold (75%)' : 'Attendance Warning: Attendance has dropped below the required 75% threshold!' }}
                </div>
                <p class="text-xs {{ $isGood ? 'text-emerald-700' : 'text-rose-700' }} mt-0.5">
                    Regular attendance is required for term examination eligibility and academic promotions.
                </p>
            </div>
        </div>
        <div class="text-right">
            <div class="text-2xl font-black {{ $isGood ? 'text-emerald-700' : 'text-rose-700' }}">
                {{ $stats['percentage'] }}%
            </div>
            <div class="text-[10px] uppercase font-bold {{ $isGood ? 'text-emerald-600' : 'text-rose-600' }}">Attendance Rate</div>
        </div>
    </div>

    <!-- Metric Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm text-center">
            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Recorded Days</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $stats['totalDays'] }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Working sessions</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm text-center">
            <div class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wider">Days Present</div>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['presentDays'] }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Full day attended</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm text-center">
            <div class="text-[11px] font-semibold text-rose-600 uppercase tracking-wider">Days Absent</div>
            <div class="text-2xl font-black text-rose-600 mt-1">{{ $stats['absentDays'] }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Unexcused missed</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm text-center">
            <div class="text-[11px] font-semibold text-amber-600 uppercase tracking-wider">Late / Excused</div>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ $stats['lateDays'] + $stats['leaveDays'] }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">{{ $stats['lateDays'] }} late, {{ $stats['leaveDays'] }} leave</div>
        </div>
    </div>

    <!-- Attendance Log Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Daily Attendance History (Last 60 Days)</h2>
                <p class="text-xs text-slate-500">Official log of class attendance recorded by faculty.</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg">
                {{ $attendances->count() }} Records
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Day</th>
                        <th class="py-3 px-4">Class / Section</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4">Notes / Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendances as $att)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                {{ \Carbon\Carbon::parse($att->date)->format('d M Y') }}
                            </td>

                            <td class="py-3 px-4 text-slate-600 font-medium">
                                {{ \Carbon\Carbon::parse($att->date)->format('l') }}
                            </td>

                            <td class="py-3 px-4 text-slate-700 font-medium">
                                {{ $enrollment?->section?->full_name ?? '—' }}
                            </td>

                            <td class="py-3 px-4 text-center">
                                @if($att->status === 'present')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase tracking-wider">
                                        ✓ Present
                                    </span>
                                @elseif($att->status === 'absent')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 uppercase tracking-wider">
                                        ✗ Absent
                                    </span>
                                @elseif($att->status === 'late')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 uppercase tracking-wider">
                                        ⏰ Late
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800 uppercase tracking-wider">
                                        ℹ Leave
                                    </span>
                                @endif
                            </td>

                            <td class="py-3 px-4 text-slate-500 text-[11px] italic">
                                {{ $att->edited_reason ?? 'Regular daily roll call' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">
                                No attendance records logged yet for this student.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
