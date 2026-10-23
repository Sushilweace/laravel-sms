@extends('layouts.app')

@section('title', 'Report Card - ' . $enrollment->student->full_name)
@section('page-title', 'Student Academic Performance Report')

@section('content')
<div class="max-w-4xl mx-auto space-y-4">

    <!-- Action Toolbar -->
    <div class="flex items-center justify-between no-print">
        <a href="{{ url()->previous() }}" class="text-xs text-slate-500 hover:text-indigo-600 font-medium">
            ← Return
        </a>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-4 py-2 rounded-xl border border-slate-200 transition">
                🖨 Print Report Card
            </button>
            <a href="{{ request()->fullUrlWithQuery(['download' => '1']) }}" class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-xl shadow-md transition">
                ⬇ Download Official PDF
            </a>
        </div>
    </div>

    <!-- Official Report Card Sheet -->
    <div class="bg-white rounded-2xl border border-slate-300 shadow-xl p-8 sm:p-12 print:border-none print:shadow-none print:p-0">

        <!-- School Header -->
        <div class="text-center border-b-2 border-slate-900 pb-6 mb-6">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight uppercase">
                {{ $school->name ?? 'Greenwood International School' }}
            </h1>
            <p class="text-xs text-slate-600 mt-1">
                {{ $school->address ?? 'Knowledge City, Bangalore' }} • Phone: {{ $school->phone ?? '+91 98765 43210' }}
            </p>
            <div class="inline-block mt-3 px-4 py-1 rounded-full bg-slate-900 text-white text-xs font-bold uppercase tracking-wider">
                Official Student Progress Report — {{ $exam->name }}
            </div>
        </div>

        <!-- Student & Session Meta -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 bg-slate-50 rounded-xl border border-slate-200 text-xs mb-6">
            <div>
                <span class="text-slate-400 font-semibold uppercase text-[10px]">Student Name</span>
                <div class="font-bold text-slate-900 text-sm mt-0.5">{{ $enrollment->student->full_name }}</div>
            </div>
            <div>
                <span class="text-slate-400 font-semibold uppercase text-[10px]">Admission / Roll</span>
                <div class="font-mono font-bold text-slate-900 text-sm mt-0.5">{{ $enrollment->student->admission_no }} (Roll #{{ $enrollment->roll_number ?? '1' }})</div>
            </div>
            <div>
                <span class="text-slate-400 font-semibold uppercase text-[10px]">Class & Section</span>
                <div class="font-bold text-slate-900 text-sm mt-0.5">{{ $enrollment->section->full_name }}</div>
            </div>
            <div>
                <span class="text-slate-400 font-semibold uppercase text-[10px]">Academic Year</span>
                <div class="font-bold text-slate-900 text-sm mt-0.5">{{ $enrollment->academicYear->name }}</div>
            </div>
        </div>

        <!-- Subject Scores Table -->
        <table class="w-full text-left text-xs border border-slate-300 rounded-lg overflow-hidden mb-6">
            <thead class="bg-slate-900 text-white font-bold uppercase tracking-wider text-[11px]">
                <tr>
                    <th class="py-2.5 px-4 border-r border-slate-800">Subject</th>
                    <th class="py-2.5 px-4 text-center border-r border-slate-800 w-24">Max Marks</th>
                    <th class="py-2.5 px-4 text-center border-r border-slate-800 w-24">Pass Marks</th>
                    <th class="py-2.5 px-4 text-center border-r border-slate-800 w-28">Marks Scored</th>
                    <th class="py-2.5 px-4">Remarks</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @foreach($marks as $m)
                <tr>
                    <td class="py-2.5 px-4 font-bold text-slate-900 border-r border-slate-200">
                        {{ $m->examSubject->subject->name }}
                    </td>
                    <td class="py-2.5 px-4 text-center border-r border-slate-200 text-slate-600 font-mono">
                        {{ number_format($m->examSubject->max_marks) }}
                    </td>
                    <td class="py-2.5 px-4 text-center border-r border-slate-200 text-slate-600 font-mono">
                        {{ number_format($m->examSubject->pass_marks) }}
                    </td>
                    <td class="py-2.5 px-4 text-center font-extrabold border-r border-slate-200 font-mono text-sm
                        {{ $m->is_absent ? 'text-rose-600' : 'text-slate-900' }}">
                        @if($m->is_absent)
                            ABSENT
                        @else
                            {{ $m->effective_marks }}
                        @endif
                    </td>
                    <td class="py-2.5 px-4 text-slate-600 italic">
                        {{ $m->teacher_remarks ?? 'Good performance' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-slate-100 font-bold border-t-2 border-slate-900 text-xs">
                <tr>
                    <td class="py-2.5 px-4 border-r border-slate-200">GRAND TOTAL</td>
                    <td class="py-2.5 px-4 text-center border-r border-slate-200 font-mono">{{ number_format($result->max_marks) }}</td>
                    <td class="py-2.5 px-4 text-center border-r border-slate-200">—</td>
                    <td class="py-2.5 px-4 text-center font-mono text-indigo-700 text-sm font-extrabold border-r border-slate-200">
                        {{ $result->total_marks }}
                    </td>
                    <td class="py-2.5 px-4 text-slate-800">
                        Overall Percentage: <strong>{{ $result->percentage }}%</strong>
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Result Summary Card -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 rounded-xl border border-slate-300 text-center text-xs mb-8">
            <div>
                <span class="text-slate-400 font-semibold uppercase text-[10px]">Final Percentage</span>
                <div class="text-xl font-extrabold text-slate-900 mt-1 font-mono">{{ $result->percentage }}%</div>
            </div>
            <div>
                <span class="text-slate-400 font-semibold uppercase text-[10px]">Grade Obtained</span>
                <div class="text-xl font-extrabold text-emerald-600 mt-1 font-mono">{{ $result->grade }}</div>
            </div>
            <div>
                <span class="text-slate-400 font-semibold uppercase text-[10px]">Class Rank</span>
                <div class="text-xl font-extrabold text-indigo-700 mt-1 font-mono">#{{ $result->class_rank ?? '1' }}</div>
            </div>
            <div>
                <span class="text-slate-400 font-semibold uppercase text-[10px]">Result Outcome</span>
                <div class="text-xl font-extrabold uppercase mt-1
                    {{ $result->result_status === 'pass' ? 'text-emerald-700' : 'text-rose-700' }}">
                    {{ $result->result_status }}
                </div>
            </div>
        </div>

        <!-- Signatures & Verification Stamp -->
        <div class="pt-8 border-t border-slate-200 grid grid-cols-3 gap-6 text-center text-xs">
            <div>
                <div class="h-10"></div>
                <div class="border-t border-slate-400 pt-1 font-semibold text-slate-700">Class Teacher</div>
            </div>
            <div>
                <div class="h-10 flex items-center justify-center">
                    <span class="px-3 py-1 rounded bg-emerald-50 border border-emerald-300 text-emerald-800 text-[10px] font-bold font-mono">
                        VERIFIED RESULT
                    </span>
                </div>
                <div class="border-t border-slate-400 pt-1 font-semibold text-slate-700">Examination Cell</div>
            </div>
            <div>
                <div class="h-10"></div>
                <div class="border-t border-slate-400 pt-1 font-semibold text-slate-700">Principal</div>
            </div>
        </div>

    </div>

</div>
@endsection
