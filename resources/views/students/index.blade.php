@extends('layouts.app')

@section('title', 'Students')
@section('page-title', 'Student Directory & Admissions')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between flex-wrap gap-4">
        <!-- Search and Filter Form -->
        <form action="{{ route('students.index') }}" method="GET" class="flex items-center gap-2 flex-wrap flex-1 max-w-2xl">
            <div class="relative flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, admission number..."
                    class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <select name="section_id" onchange="this.form.submit()" class="text-xs px-3 py-2 rounded-xl border border-slate-300 focus:outline-none">
                <option value="">All Sections</option>
                @foreach($sections as $sec)
                <option value="{{ $sec->id }}" {{ request('section_id') == $sec->id ? 'selected' : '' }}>
                    {{ $sec->schoolClass->name }} - {{ $sec->name }}
                </option>
                @endforeach
            </select>

            <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white text-xs px-3.5 py-2 rounded-xl font-medium transition">
                Filter
            </button>
            @if(request()->hasAny(['search', 'section_id']))
            <a href="{{ route('students.index') }}" class="text-xs text-slate-500 hover:text-slate-700 py-2">Clear</a>
            @endif
        </form>

        <!-- Admission & Promotion Buttons -->
        <div class="flex items-center gap-2">
            @can('students.promote')
            <a href="{{ route('students.promote') }}" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium px-3.5 py-2 rounded-xl border border-slate-200 transition">
                🚀 Year-End Promotion
            </a>
            @endcan
            @can('students.create')
            <a href="{{ route('students.create') }}" class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-xl shadow-md shadow-indigo-600/20 transition flex items-center gap-1.5">
                <span>+ Admit Student</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Student Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Student</th>
                        <th class="py-3 px-4">Admission No</th>
                        <th class="py-3 px-4">Class & Section</th>
                        <th class="py-3 px-4">Roll No</th>
                        <th class="py-3 px-4">Gender</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($students as $st)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-xs">
                                    {{ substr($st->first_name, 0, 1) }}
                                </div>
                                <div>
                                    <a href="{{ route('students.show', $st) }}" class="font-bold text-slate-900 hover:text-indigo-600 transition">
                                        {{ $st->full_name }}
                                    </a>
                                    <div class="text-[10px] text-slate-400">DOB: {{ $st->date_of_birth->format('d M Y') }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4 font-mono font-medium text-slate-600">{{ $st->admission_no }}</td>
                        <td class="py-3 px-4 text-slate-700">
                            {{ $st->currentEnrollment?->section?->full_name ?? 'Not Assigned' }}
                        </td>
                        <td class="py-3 px-4 font-semibold text-slate-700">{{ $st->currentEnrollment?->roll_number ?? '—' }}</td>
                        <td class="py-3 px-4 capitalize text-slate-600">{{ $st->gender }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold
                                {{ $st->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ ucfirst($st->status) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('students.show', $st) }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold px-2 py-1 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition">
                                View Profile →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500">
                            No student records found matching the criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $students->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
