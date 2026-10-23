@extends('layouts.app')

@section('title', $exam->name)
@section('page-title', 'Exam Control Center: ' . $exam->name)

@section('content')
<div class="space-y-6">

    <!-- Header & Lifecycle Actions Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-bold text-slate-900">{{ $exam->name }}</h2>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                        {{ $exam->status === 'published' ? 'bg-emerald-100 text-emerald-700' : '' }}
                        {{ $exam->status === 'marks_open' ? 'bg-blue-100 text-blue-700' : '' }}
                        {{ $exam->status === 'locked' ? 'bg-amber-100 text-amber-700' : '' }}
                        {{ $exam->status === 'draft' ? 'bg-slate-100 text-slate-700' : '' }}">
                        {{ strtoupper(str_replace('_', ' ', $exam->status)) }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Dates: {{ $exam->start_date->format('d M Y') }} - {{ $exam->end_date->format('d M Y') }} |
                    Minimum Pass: {{ $exam->pass_percentage }}% |
                    Annual Weightage: {{ $exam->weightage ?? '100' }}%
                </p>
            </div>

            <!-- State Machine Action Buttons -->
            <div class="flex items-center gap-2 flex-wrap text-xs">
                @if($exam->isDraft())
                <form action="{{ route('exams.open-marks', $exam) }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-xl transition">
                        ▶ Open Marks Entry
                    </button>
                </form>
                @endif

                @if($exam->isMarksOpen())
                <form action="{{ route('exams.lock', $exam) }}" method="POST" onsubmit="return confirm('Lock exam marks? Teachers will no longer be able to edit scores without special permission.')">
                    @csrf
                    <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-semibold px-4 py-2 rounded-xl transition">
                        🔒 Lock Marks Entry
                    </button>
                </form>
                @endif

                @if($exam->isLocked() || $exam->isPublished())
                <form action="{{ route('exams.compute', $exam) }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-xl transition">
                        ⚡ Compute Results & Ranks
                    </button>
                </form>
                @endif

                @if($exam->isLocked())
                <form action="{{ route('exams.publish', $exam) }}" method="POST" onsubmit="return confirm('Publish results? Students and parents will be able to view their final grades and report cards.')">
                    @csrf
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-2 rounded-xl transition">
                        ✓ Publish Results to Parents
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Exam Subjects & Marks Entry Links -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 text-xs">
        <h3 class="font-bold text-slate-900 text-sm mb-4">Exam Subjects & Marks Sheets</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            @foreach($exam->examSubjects as $es)
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between">
                <div>
                    <div class="font-bold text-sm text-slate-900">{{ $es->subject->name }}</div>
                    <div class="text-slate-500 mt-0.5">{{ $es->schoolClass->name }} (Max: {{ $es->max_marks }})</div>
                </div>
                <a href="{{ route('exams.enter-marks', [$exam, $es]) }}" class="bg-white hover:bg-indigo-50 text-indigo-600 font-semibold px-3 py-1.5 rounded-lg border border-slate-200 hover:border-indigo-200 transition">
                    Marks Sheet →
                </a>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Consolidated Computed Results & Ranks -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="font-bold text-slate-900 text-sm">Official Consolidated Results & Marksheets</h3>
                <p class="text-xs text-slate-500 mt-0.5">Computed using CBSE Standard Grade Scale with rank ties skipped.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if($results->isNotEmpty())
                    <a href="{{ route('exams.class-marksheet', $exam) }}" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-3 py-1.5 rounded-lg border border-slate-200 transition flex items-center gap-1">
                        👁 View Tabulation Register
                    </a>
                    <a href="{{ route('exams.class-marksheet', [$exam, 'format' => 'csv']) }}" class="text-xs bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold px-3 py-1.5 rounded-lg border border-emerald-200 transition flex items-center gap-1">
                        📊 Export CSV
                    </a>
                    <a href="{{ route('exams.class-marksheet', [$exam, 'download' => '1']) }}" class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-3.5 py-1.5 rounded-lg shadow-sm transition flex items-center gap-1">
                        ⬇ Download Class Marksheet (PDF)
                    </a>
                @endif
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Class Rank</th>
                        <th class="py-3 px-4">Section Rank</th>
                        <th class="py-3 px-4">Student</th>
                        <th class="py-3 px-4">Class / Section</th>
                        <th class="py-3 px-4">Total Marks</th>
                        <th class="py-3 px-4">Percentage</th>
                        <th class="py-3 px-4">Grade</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Report Card</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($results as $res)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4 font-extrabold text-slate-900 font-mono">
                            #{{ $res->class_rank ?? '—' }}
                        </td>
                        <td class="py-3 px-4 font-bold text-slate-600 font-mono">
                            #{{ $res->section_rank ?? '—' }}
                        </td>
                        <td class="py-3 px-4 font-bold text-slate-900">
                            {{ $res->enrollment->student->full_name }}
                        </td>
                        <td class="py-3 px-4 text-slate-600">
                            {{ $res->enrollment->section->full_name }}
                        </td>
                        <td class="py-3 px-4 font-semibold text-slate-800">
                            {{ $res->total_marks }} / {{ $res->max_marks }}
                        </td>
                        <td class="py-3 px-4 font-bold text-indigo-600 font-mono">
                            {{ $res->percentage }}%
                        </td>
                        <td class="py-3 px-4 font-extrabold text-emerald-600 font-mono">
                            {{ $res->grade }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold uppercase
                                {{ $res->result_status === 'pass' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                {{ $res->result_status }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('exams.report-card', [$exam, $res->enrollment]) }}" class="text-xs text-slate-600 hover:text-slate-900 font-medium px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg transition" title="Preview Report Card">
                                    Preview
                                </a>
                                <a href="{{ route('exams.report-card', [$exam, $res->enrollment, 'download' => '1']) }}" class="text-xs text-indigo-700 hover:text-indigo-900 font-semibold px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 rounded-lg border border-indigo-200 transition flex items-center gap-1" title="Download Official Marksheet PDF">
                                    <span>⬇</span> Marksheet PDF
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-8 text-center text-slate-500">
                            Results not yet computed. Click "⚡ Compute Results & Ranks" above after marks are entered.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
