@extends('layouts.app')

@section('title', 'Daily Attendance')
@section('page-title', 'Daily Attendance Register')

@section('content')
<div class="space-y-6">

    <!-- Filter Bar: Section and Date -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between flex-wrap gap-4">
        <form action="{{ route('attendance.index') }}" method="GET" class="flex items-center gap-3 flex-wrap">
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Select Section</label>
                <select name="section_id" onchange="this.form.submit()" class="text-xs px-3 py-2 rounded-xl border border-slate-300">
                    @foreach($sections as $sec)
                    <option value="{{ $sec->id }}" {{ $selectedSectionId == $sec->id ? 'selected' : '' }}>
                        {{ $sec->full_name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Attendance Date</label>
                <input type="date" name="date" value="{{ $selectedDate }}" onchange="this.form.submit()"
                    class="text-xs px-3 py-2 rounded-xl border border-slate-300">
            </div>

            <div class="pt-5">
                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white text-xs px-3.5 py-2 rounded-xl font-medium transition">
                    Load Sheet
                </button>
            </div>
        </form>

        <a href="{{ route('attendance.reports', ['section_id' => $selectedSectionId]) }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold bg-indigo-50 px-3.5 py-2 rounded-xl border border-indigo-200 transition">
            Monthly Reports & Summaries →
        </a>
    </div>

    @if($selectedSectionId && $students->isNotEmpty())
    <!-- Register Sheet -->
    <form action="{{ route('attendance.save') }}" method="POST">
        @csrf
        <input type="hidden" name="section_id" value="{{ $selectedSectionId }}">
        <input type="hidden" name="date" value="{{ $selectedDate }}">

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <!-- Sheet Header with Quick Actions -->
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between flex-wrap gap-4 text-xs">
                <div class="flex items-center gap-3">
                    <span class="font-bold text-slate-900">{{ $students->count() }} Enrolled Students</span>
                    @if($isFinalized)
                        <span class="bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded font-semibold flex items-center gap-1">
                            ✓ Submitted at {{ $attendanceDay->finalized_at->format('h:i A') }}
                        </span>
                    @else
                        <span class="bg-amber-100 text-amber-800 px-2 py-0.5 rounded font-semibold">
                            Pending Submission
                        </span>
                    @endif
                </div>

                <!-- Fast Bulk Selection Buttons -->
                <div class="flex items-center gap-2">
                    <button type="button" onclick="setAll('present')" class="bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold px-2.5 py-1 rounded-lg border border-emerald-200 transition">
                        Mark All Present
                    </button>
                    <button type="button" onclick="setAll('absent')" class="bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold px-2.5 py-1 rounded-lg border border-rose-200 transition">
                        Mark All Absent
                    </button>
                </div>
            </div>

            <!-- Student List Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100/70 text-slate-600 uppercase tracking-wider font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-4 w-16">Roll</th>
                            <th class="py-2.5 px-4">Student</th>
                            <th class="py-2.5 px-4">Admission No</th>
                            <th class="py-2.5 px-4 text-center">Attendance Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($students as $enr)
                        @php
                            $currentStatus = $existingRecords->get($enr->id)?->status ?? 'present';
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4 font-bold text-slate-700 font-mono">{{ $enr->roll_number ?? '—' }}</td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $enr->student->full_name }}</div>
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-500">{{ $enr->student->admission_no }}</td>
                            <td class="py-3 px-4 text-center">
                                <div class="inline-flex rounded-xl bg-slate-100 p-1 border border-slate-200 gap-1">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="attendance[{{ $enr->id }}]" value="present" class="sr-only peer status-radio status-present"
                                            {{ $currentStatus === 'present' ? 'checked' : '' }}>
                                        <span class="px-3 py-1 rounded-lg text-xs font-semibold block transition peer-checked:bg-emerald-600 peer-checked:text-white text-slate-600">
                                            Present
                                        </span>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="attendance[{{ $enr->id }}]" value="late" class="sr-only peer status-radio status-late"
                                            {{ $currentStatus === 'late' ? 'checked' : '' }}>
                                        <span class="px-3 py-1 rounded-lg text-xs font-semibold block transition peer-checked:bg-amber-500 peer-checked:text-white text-slate-600">
                                            Late
                                        </span>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="attendance[{{ $enr->id }}]" value="absent" class="sr-only peer status-radio status-absent"
                                            {{ $currentStatus === 'absent' ? 'checked' : '' }}>
                                        <span class="px-3 py-1 rounded-lg text-xs font-semibold block transition peer-checked:bg-rose-600 peer-checked:text-white text-slate-600">
                                            Absent
                                        </span>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="attendance[{{ $enr->id }}]" value="leave" class="sr-only peer status-radio status-leave"
                                            {{ $currentStatus === 'leave' ? 'checked' : '' }}>
                                        <span class="px-3 py-1 rounded-lg text-xs font-semibold block transition peer-checked:bg-blue-600 peer-checked:text-white text-slate-600">
                                            Leave
                                        </span>
                                    </label>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Footer Save Bar -->
            <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between flex-wrap gap-4">
                <div class="flex-1 max-w-md">
                    <input type="text" name="edit_reason" placeholder="Reason for past date edit (if modifying older dates)"
                        class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300">
                </div>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs px-6 py-2.5 rounded-xl shadow-lg shadow-indigo-600/20 transition">
                    Save & Submit Daily Attendance
                </button>
            </div>
        </div>
    </form>
    @else
    <div class="bg-white p-8 rounded-2xl border border-slate-200 text-center text-sm text-slate-500">
        No active students enrolled in this section.
    </div>
    @endif

</div>

<script>
    function setAll(status) {
        document.querySelectorAll('.status-radio.status-' + status).forEach(el => el.checked = true);
    }
</script>
@endsection
