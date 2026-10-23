@extends('layouts.app')

@section('title', 'Examinations')
@section('page-title', 'Exams & Marks Management')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between flex-wrap gap-4">
        <div>
            <h3 class="font-bold text-slate-900 text-sm">Examinations ({{ $currentYear?->name }})</h3>
            <p class="text-xs text-slate-500 mt-0.5">Manage exam lifecycle from draft to computed published report cards.</p>
        </div>

        @can('exams.manage')
        <a href="{{ route('exams.create') }}" class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-xl shadow-md transition">
            + Create New Exam
        </a>
        @endcan
    </div>

    <!-- Exams Grid/List -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($exams as $exam)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 hover:border-indigo-200 transition">
            <div class="flex items-start justify-between">
                <div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                        {{ $exam->status === 'published' ? 'bg-emerald-100 text-emerald-700' : '' }}
                        {{ $exam->status === 'marks_open' ? 'bg-blue-100 text-blue-700' : '' }}
                        {{ $exam->status === 'locked' ? 'bg-amber-100 text-amber-700' : '' }}
                        {{ $exam->status === 'draft' ? 'bg-slate-100 text-slate-700' : '' }}">
                        {{ strtoupper(str_replace('_', ' ', $exam->status)) }}
                    </span>
                    <h3 class="text-lg font-bold text-slate-900 mt-2">{{ $exam->name }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $exam->term ?? 'Regular Exam' }} • {{ $exam->start_date->format('d M') }} - {{ $exam->end_date->format('d M Y') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2 my-4 text-xs bg-slate-50 p-3 rounded-xl border border-slate-200 text-center">
                <div>
                    <span class="text-slate-400 text-[10px] uppercase font-bold">Pass %</span>
                    <div class="font-bold text-slate-800">{{ $exam->pass_percentage }}%</div>
                </div>
                <div>
                    <span class="text-slate-400 text-[10px] uppercase font-bold">Weightage</span>
                    <div class="font-bold text-slate-800">{{ $exam->weightage ?? '100' }}%</div>
                </div>
                <div>
                    <span class="text-slate-400 text-[10px] uppercase font-bold">Grading</span>
                    <div class="font-bold text-indigo-600 truncate">{{ $exam->gradeScale?->name ?? 'Standard' }}</div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                <span class="text-xs text-slate-400">
                    {{ $exam->results_computed_at ? 'Results Computed' : 'Results Pending' }}
                </span>
                <a href="{{ route('exams.show', $exam) }}" class="text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold px-4 py-2 rounded-xl border border-indigo-200 transition">
                    Open Exam Control Center →
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-2 bg-white p-8 rounded-2xl border border-slate-200 text-center text-sm text-slate-500">
            No examinations configured for this academic year.
        </div>
        @endforelse
    </div>

</div>
@endsection
