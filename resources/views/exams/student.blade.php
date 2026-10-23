@extends('layouts.app')

@section('title', 'My Examination Results & Marksheets')

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
                        Academic Performance & Grades
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

    <!-- Exam Performance Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Published Examinations</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $publishedExams->count() }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Formal evaluations completed</div>
        </div>

        @php
            $latestResult = $results->first();
        @endphp

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="text-[11px] font-semibold text-indigo-600 uppercase tracking-wider">Latest Term Percentage</div>
            <div class="text-2xl font-black text-indigo-600 mt-1">
                {{ $latestResult ? $latestResult->percentage . '%' : '—' }}
            </div>
            <div class="text-[10px] text-slate-400 mt-0.5">
                Grade: <strong class="text-slate-700">{{ $latestResult?->grade ?? 'Pending' }}</strong>
                @if($latestResult?->gpa)
                    (GPA: {{ $latestResult->gpa }})
                @endif
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wider">Class Standing & Rank</div>
            <div class="text-2xl font-black text-emerald-600 mt-1">
                {{ $latestResult && $latestResult->section_rank ? 'Rank #' . $latestResult->section_rank : '—' }}
            </div>
            <div class="text-[10px] text-slate-400 mt-0.5">
                Status: <strong class="uppercase text-slate-700">{{ $latestResult?->result_status ?? 'Pending' }}</strong>
            </div>
        </div>
    </div>

    <!-- Examinations & Marksheet Downloads List -->
    <div class="space-y-4">
        <h2 class="text-sm font-bold text-slate-900">Examination Results & Official Marksheets</h2>

        @forelse($publishedExams as $exam)
            @php
                $result = $results->firstWhere('exam_id', $exam->id);
            @endphp
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col md:flex-row md:items-center justify-between gap-6 hover:border-indigo-300 transition">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 uppercase tracking-wider">
                            {{ $exam->term ?? 'Term Exam' }}
                        </span>
                        <span class="text-xs font-mono text-slate-400">
                            {{ \Carbon\Carbon::parse($exam->start_date)->format('d M') }} — {{ \Carbon\Carbon::parse($exam->end_date)->format('d M Y') }}
                        </span>
                    </div>

                    <h3 class="text-base font-extrabold text-slate-900 mt-1">{{ $exam->name }}</h3>
                    <p class="text-xs text-slate-500">
                        Pass Requirement: {{ $exam->pass_percentage }}% • Grading Scale: {{ $exam->gradeScale->name ?? 'Standard CBSE' }}
                    </p>

                    @if($result)
                        <div class="flex items-center gap-4 text-xs font-mono pt-2 flex-wrap">
                            <span class="text-slate-700">Marks: <strong>{{ (float) $result->total_marks }}</strong> / {{ (int) $result->max_marks }}</span>
                            <span class="text-indigo-600 font-bold">Percentage: {{ $result->percentage }}%</span>
                            <span class="text-emerald-700 font-bold">Grade: {{ $result->grade }}</span>
                            @if($result->section_rank)
                                <span class="text-slate-500">Section Rank: #{{ $result->section_rank }}</span>
                            @endif
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $result->result_status === 'pass' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ $result->result_status }}
                            </span>
                        </div>
                    @else
                        <div class="text-xs text-slate-400 italic pt-1">
                            Your result computation for this exam is currently being processed by faculty.
                        </div>
                    @endif
                </div>

                <div class="flex items-center gap-2.5 flex-shrink-0">
                    @if($result && $enrollment)
                        <a href="{{ route('exams.marksheet', [$exam, $enrollment->id]) }}"
                           class="text-xs font-semibold px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 transition">
                            👁 View Marksheet
                        </a>

                        <a href="{{ route('exams.marksheet', [$exam, $enrollment->id, 'download' => 1]) }}"
                           class="text-xs font-semibold px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition flex items-center gap-1.5">
                            <span>⬇</span> Download Marksheet (PDF)
                        </a>
                    @else
                        <span class="text-xs font-semibold px-4 py-2 rounded-xl bg-slate-100 text-slate-400">
                            Marksheet Unavailable
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-slate-400">
                No published examinations found for your class section yet.
            </div>
        @endforelse
    </div>
</div>
@endsection
