@extends('layouts.app')

@section('title', 'Class Marksheet - ' . $exam->name . ' - ' . $section->full_name)

@section('content')
<div class="space-y-6">

    <!-- Action & Filter Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm no-print">
        <div class="flex items-center gap-3">
            <a href="{{ route('exams.show', $exam) }}" class="text-xs text-slate-500 hover:text-indigo-600 font-semibold px-3 py-2 bg-slate-50 hover:bg-indigo-50 rounded-xl border border-slate-200 transition">
                ← Back to Exam
            </a>
            <div>
                <h1 class="text-lg font-extrabold text-slate-900">
                    Consolidated Class Marksheet
                </h1>
                <p class="text-xs text-slate-500">
                    {{ $exam->name }} • {{ $section->full_name }}
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Section Selector Dropdown -->
            @if($sections->count() > 1)
            <form method="GET" action="{{ route('exams.class-marksheet', $exam) }}" class="flex items-center gap-2">
                <select name="section_id" onchange="this.form.submit()" class="text-xs font-semibold rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 py-2">
                    @foreach($sections as $sec)
                        <option value="{{ $sec->id }}" {{ $sec->id === $section->id ? 'selected' : '' }}>
                            {{ $sec->full_name }}
                        </option>
                    @endforeach
                </select>
            </form>
            @endif

            <button onclick="window.print()" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl border border-slate-200 transition flex items-center gap-1.5">
                <span>🖨</span> Print Marksheet
            </button>

            <a href="{{ request()->fullUrlWithQuery(['format' => 'csv']) }}" class="text-xs bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold px-3.5 py-2 rounded-xl border border-emerald-200 transition flex items-center gap-1.5">
                <span>📊</span> Export CSV
            </a>

            <a href="{{ request()->fullUrlWithQuery(['download' => '1']) }}" class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-xl shadow-sm hover:shadow transition flex items-center gap-1.5">
                <span>⬇</span> Download Marksheet (PDF)
            </a>
        </div>
    </div>

    <!-- Marksheet Container -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-6 print:border-none print:shadow-none print:p-0">
        
        <!-- School & Exam Title (Printable Header) -->
        <div class="text-center border-b-2 border-slate-900 pb-4 mb-4">
            <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight">
                {{ $school->name ?? 'Greenwood International School' }}
            </h2>
            <div class="text-xs text-slate-600 mt-0.5">
                {{ $school->address ?? 'Knowledge City, Bangalore' }} • Phone: {{ $school->phone ?? '+91 98765 43210' }}
            </div>
            <div class="inline-block mt-2 px-3 py-1 bg-slate-900 text-white text-xs font-bold uppercase tracking-wider rounded-md">
                Official Consolidated Marksheet & Tabulation Register — {{ $exam->name }}
            </div>
        </div>

        <!-- Meta Summary Bar -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs mb-4">
            <div>
                <span class="text-slate-400 font-semibold uppercase text-[10px]">Class & Section</span>
                <div class="font-bold text-slate-900 text-sm mt-0.5">{{ $section->full_name }}</div>
            </div>
            <div>
                <span class="text-slate-400 font-semibold uppercase text-[10px]">Academic Year</span>
                <div class="font-bold text-slate-900 text-sm mt-0.5">{{ $exam->academicYear->name }}</div>
            </div>
            <div>
                <span class="text-slate-400 font-semibold uppercase text-[10px]">Class Teacher</span>
                <div class="font-bold text-slate-900 text-sm mt-0.5">{{ $section->classTeacher->user->name ?? 'Not Assigned' }}</div>
            </div>
            <div>
                <span class="text-slate-400 font-semibold uppercase text-[10px]">Enrolled Students</span>
                <div class="font-bold text-slate-900 text-sm mt-0.5">{{ $enrollments->count() }} Students</div>
            </div>
        </div>

        <!-- Marksheet Tabulation Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-center border-collapse">
                <thead class="bg-slate-100 text-slate-700 uppercase font-bold border border-slate-300">
                    <tr>
                        <th class="py-2 px-2 border border-slate-300 w-12">Roll</th>
                        <th class="py-2 px-2 border border-slate-300 w-24">Adm No</th>
                        <th class="py-2 px-3 border border-slate-300 text-left min-w-[150px]">Student Name</th>
                        @foreach($subjects as $es)
                            <th class="py-2 px-2 border border-slate-300">
                                <div>{{ $es->subject->code ?? substr($es->subject->name, 0, 4) }}</div>
                                <div class="text-[10px] text-slate-400 font-normal">Max: {{ (int)$es->max_marks }}</div>
                            </th>
                        @endforeach
                        <th class="py-2 px-2 border border-slate-300 w-20">Total</th>
                        <th class="py-2 px-2 border border-slate-300 w-14">%</th>
                        <th class="py-2 px-2 border border-slate-300 w-14">Grade</th>
                        <th class="py-2 px-2 border border-slate-300 w-12">GPA</th>
                        <th class="py-2 px-2 border border-slate-300 w-14">Rank</th>
                        <th class="py-2 px-2 border border-slate-300 w-20">Result</th>
                        <th class="py-2 px-2 border border-slate-300 w-24 no-print">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($enrollments as $enrollment)
                        @php
                            $res = $results[$enrollment->id] ?? null;
                            $stMarks = $marks[$enrollment->id] ?? collect();
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-2 px-2 border border-slate-200 font-bold font-mono">{{ $enrollment->roll_number ?? '—' }}</td>
                            <td class="py-2 px-2 border border-slate-200 font-mono text-slate-600">{{ $enrollment->student->admission_no }}</td>
                            <td class="py-2 px-3 border border-slate-200 text-left font-bold text-slate-900">
                                {{ $enrollment->student->full_name }}
                            </td>

                            @foreach($subjects as $es)
                                @php
                                    $m = $stMarks->firstWhere('exam_subject_id', $es->id);
                                @endphp
                                <td class="py-2 px-2 border border-slate-200 font-mono">
                                    @if(!$m)
                                        <span class="text-slate-300">—</span>
                                    @elseif($m->is_absent)
                                        <span class="text-rose-600 font-bold">AB</span>
                                    @else
                                        <span class="{{ (float)$m->final_marks < ($es->pass_marks ?? 33) ? 'text-rose-600 font-bold' : 'text-slate-800' }}">
                                            {{ (float)$m->final_marks }}
                                        </span>
                                    @endif
                                </td>
                            @endforeach

                            <td class="py-2 px-2 border border-slate-200 font-bold text-slate-900">
                                {{ $res ? (float)$res->total_marks . ' / ' . (int)$res->max_marks : '—' }}
                            </td>
                            <td class="py-2 px-2 border border-slate-200 font-bold text-indigo-600 font-mono">
                                {{ $res ? number_format((float)$res->percentage, 1) . '%' : '—' }}
                            </td>
                            <td class="py-2 px-2 border border-slate-200 font-black text-emerald-600">
                                {{ $res->grade ?? '—' }}
                            </td>
                            <td class="py-2 px-2 border border-slate-200 font-mono text-slate-700">
                                {{ $res->gpa ?? '—' }}
                            </td>
                            <td class="py-2 px-2 border border-slate-200 font-bold text-slate-900 font-mono">
                                #{{ $res->section_rank ?? '—' }}
                            </td>
                            <td class="py-2 px-2 border border-slate-200">
                                @if($res)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                        {{ $res->result_status === 'pass' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ $res->result_status }}
                                    </span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="py-2 px-2 border border-slate-200 no-print">
                                <a href="{{ route('exams.report-card', [$exam, $enrollment, 'download' => '1']) }}" 
                                   title="Download Individual Student Marksheet PDF"
                                   class="text-[11px] text-indigo-600 hover:text-indigo-800 font-semibold px-2 py-1 bg-indigo-50 hover:bg-indigo-100 rounded-md border border-indigo-200 transition inline-flex items-center gap-1">
                                    <span>⬇</span> PDF
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 10 + count($subjects) }}" class="py-8 text-center text-slate-400">
                                No active students found in this section.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Class Performance Statistics Summary -->
        <div class="mt-4 p-4 bg-slate-50 rounded-xl border border-slate-200">
            <div class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Class Performance Summary</div>
            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-8 gap-3 text-center text-xs">
                <div class="p-2 bg-white rounded-lg border border-slate-200">
                    <span class="text-slate-400 text-[10px] block">Strength</span>
                    <strong class="text-sm text-slate-900">{{ $stats['total'] }}</strong>
                </div>
                <div class="p-2 bg-white rounded-lg border border-slate-200">
                    <span class="text-slate-400 text-[10px] block">Appeared</span>
                    <strong class="text-sm text-slate-900">{{ $stats['appeared'] }}</strong>
                </div>
                <div class="p-2 bg-white rounded-lg border border-slate-200">
                    <span class="text-emerald-500 text-[10px] block">Passed</span>
                    <strong class="text-sm text-emerald-600">{{ $stats['passed'] }}</strong>
                </div>
                <div class="p-2 bg-white rounded-lg border border-slate-200">
                    <span class="text-rose-500 text-[10px] block">Failed</span>
                    <strong class="text-sm text-rose-600">{{ $stats['failed'] }}</strong>
                </div>
                <div class="p-2 bg-white rounded-lg border border-slate-200">
                    <span class="text-indigo-500 text-[10px] block">Pass Rate</span>
                    <strong class="text-sm text-indigo-600">{{ $stats['pass_percentage'] }}%</strong>
                </div>
                <div class="p-2 bg-white rounded-lg border border-slate-200">
                    <span class="text-slate-400 text-[10px] block">Average</span>
                    <strong class="text-sm text-slate-900">{{ $stats['average_percentage'] }}%</strong>
                </div>
                <div class="p-2 bg-white rounded-lg border border-slate-200">
                    <span class="text-emerald-500 text-[10px] block">Highest</span>
                    <strong class="text-sm text-emerald-600">{{ $stats['highest_percentage'] }}%</strong>
                </div>
                <div class="p-2 bg-white rounded-lg border border-slate-200">
                    <span class="text-rose-500 text-[10px] block">Lowest</span>
                    <strong class="text-sm text-rose-600">{{ $stats['lowest_percentage'] }}%</strong>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
