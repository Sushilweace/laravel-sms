@extends('layouts.app')

@section('title', 'Attendance Reports')
@section('page-title', 'Monthly Attendance Reports & Defaulters')

@section('content')
<div class="space-y-6">

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between flex-wrap gap-4">
        <form action="{{ route('attendance.reports') }}" method="GET" class="flex items-center gap-3">
            <select name="section_id" onchange="this.form.submit()" class="text-xs px-3 py-2 rounded-xl border border-slate-300">
                @foreach($sections as $sec)
                <option value="{{ $sec->id }}" {{ $selectedSectionId == $sec->id ? 'selected' : '' }}>
                    {{ $sec->full_name }}
                </option>
                @endforeach
            </select>
        </form>

        <a href="{{ route('attendance.index', ['section_id' => $selectedSectionId]) }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold">
            ← Return to Daily Register
        </a>
    </div>

    <!-- Summaries Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Student</th>
                        <th class="py-3 px-4">Month</th>
                        <th class="py-3 px-4">Working Days</th>
                        <th class="py-3 px-4">Present</th>
                        <th class="py-3 px-4">Late</th>
                        <th class="py-3 px-4">Absent</th>
                        <th class="py-3 px-4">Leave</th>
                        <th class="py-3 px-4">Attendance %</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($summaries as $sum)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4 font-bold text-slate-900">
                            {{ $sum->enrollment->student->full_name }}
                        </td>
                        <td class="py-3 px-4 text-slate-600">{{ $sum->month->format('M Y') }}</td>
                        <td class="py-3 px-4 font-semibold text-slate-700">{{ $sum->working_days }}</td>
                        <td class="py-3 px-4 text-emerald-600 font-bold">{{ $sum->present }}</td>
                        <td class="py-3 px-4 text-amber-600 font-bold">{{ $sum->late }}</td>
                        <td class="py-3 px-4 text-rose-600 font-bold">{{ $sum->absent }}</td>
                        <td class="py-3 px-4 text-blue-600 font-bold">{{ $sum->leave }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold
                                {{ $sum->percentage < $lowThreshold ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                                {{ $sum->percentage }}%
                                @if($sum->percentage < $lowThreshold)
                                <span title="Below mandatory {{ $lowThreshold }}% threshold">⚠ Low</span>
                                @endif
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-500">
                            No monthly summaries calculated yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
