@extends('layouts.app')

@section('title', 'Enter Marks')
@section('page-title', 'Marks Entry: ' . $examSubject->subject->name . ' (' . $exam->name . ')')

@section('content')
<div class="space-y-6">

    <!-- Top Info Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between flex-wrap gap-4">
        <div>
            <a href="{{ route('exams.show', $exam) }}" class="text-xs text-slate-500 hover:text-indigo-600 font-medium">
                ← Back to {{ $exam->name }}
            </a>
            <h3 class="font-bold text-slate-900 text-sm mt-1">
                {{ $examSubject->subject->name }} — {{ $examSubject->schoolClass->name }}
            </h3>
            <p class="text-xs text-slate-500">Max Marks: <strong>{{ $examSubject->max_marks }}</strong> | Pass Marks: <strong>{{ $examSubject->pass_marks }}</strong></p>
        </div>

        <form action="{{ route('exams.enter-marks', [$exam, $examSubject]) }}" method="GET" class="flex items-center gap-2">
            <label class="text-xs text-slate-500 font-medium">Section:</label>
            <select name="section_id" onchange="this.form.submit()" class="text-xs px-3 py-1.5 rounded-xl border border-slate-300">
                @foreach($sections as $sec)
                <option value="{{ $sec->id }}" {{ $selectedSectionId == $sec->id ? 'selected' : '' }}>
                    Section {{ $sec->name }}
                </option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Marks Entry Form Table -->
    <form action="{{ route('exams.save-marks', [$exam, $examSubject]) }}" method="POST">
        @csrf
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase tracking-wider font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-4 w-16">Roll</th>
                            <th class="py-2.5 px-4">Student</th>
                            <th class="py-2.5 px-4 text-center w-28">Absent?</th>
                            <th class="py-2.5 px-4 w-44">Score (Max {{ $examSubject->max_marks }})</th>
                            <th class="py-2.5 px-4">Teacher Remark</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($enrollments as $enr)
                        @php
                            $m = $marks->get($enr->id);
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-bold text-slate-700 font-mono">{{ $enr->roll_number ?? '—' }}</td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $enr->student->full_name }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $enr->student->admission_no }}</div>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <label class="inline-flex items-center gap-1.5 cursor-pointer text-slate-600 font-medium">
                                    <input type="checkbox" name="absent[{{ $enr->id }}]" value="1"
                                        {{ $m?->is_absent ? 'checked' : '' }}
                                        class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                                    <span class="text-xs">Absent</span>
                                </label>
                            </td>
                            <td class="py-3 px-4">
                                <input type="number" step="0.01" min="0" max="{{ $examSubject->max_marks }}"
                                    name="marks[{{ $enr->id }}]"
                                    value="{{ $m?->marks_obtained }}"
                                    placeholder="0 - {{ $examSubject->max_marks }}"
                                    class="w-32 px-3 py-1.5 rounded-lg border border-slate-300 font-mono font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </td>
                            <td class="py-3 px-4">
                                <input type="text" name="remarks[{{ $enr->id }}]"
                                    value="{{ $m?->teacher_remarks }}"
                                    placeholder="Constructive feedback..."
                                    class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs">
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500">
                                No active students in this section.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer Save Bar -->
            <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between flex-wrap gap-4">
                <div class="flex-1 max-w-md">
                    @if($exam->isLocked() || $exam->isPublished())
                    <input type="text" name="change_reason" required placeholder="Mandatory reason for modifying locked/published exam scores..."
                        class="w-full text-xs px-3 py-2 rounded-xl border border-amber-300 bg-amber-50/50">
                    @endif
                </div>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs px-6 py-2.5 rounded-xl shadow-lg shadow-indigo-600/20 transition">
                    Save Marks Sheet
                </button>
            </div>
        </div>
    </form>

</div>
@endsection
