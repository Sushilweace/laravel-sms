@extends('layouts.app')

@section('title', $staff->full_name)
@section('page-title', 'Staff Member: ' . $staff->full_name)

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <div class="flex items-center justify-between">
        <a href="{{ route('staff.index') }}" class="text-xs text-slate-500 hover:text-indigo-600 font-medium">
            ← Back to Staff Directory
        </a>
    </div>

    <!-- Profile Summary Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="flex items-center gap-5">
            <div class="w-16 h-16 rounded-2xl bg-purple-600 text-white font-extrabold text-2xl flex items-center justify-center shadow-lg shadow-purple-600/20">
                {{ substr($staff->first_name, 0, 1) }}
            </div>
            <div>
                <h2 class="text-xl font-bold text-slate-900">{{ $staff->full_name }}</h2>
                <div class="text-xs text-slate-500 mt-0.5 flex items-center gap-3">
                    <span>Code: <strong class="font-mono text-slate-700">{{ $staff->employee_code }}</strong></span>
                    <span>•</span>
                    <span>{{ $staff->designation }}</span>
                    <span>•</span>
                    <span class="text-indigo-600 font-semibold">{{ $staff->user?->getRoleNames()->first() ?? 'Staff' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Teaching Assignments and Classes -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 text-xs">
        <h3 class="font-bold text-slate-900 text-sm mb-4 border-b border-slate-100 pb-2">Assigned Classes & Subjects</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @forelse($staff->teachingAssignments as $assign)
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between">
                <div>
                    <div class="font-bold text-sm text-slate-900">{{ $assign->subject->name }}</div>
                    <div class="text-slate-500 mt-1">{{ $assign->section->full_name }}</div>
                </div>
                <span class="px-2 py-1 bg-white rounded font-mono font-bold text-[10px] border border-slate-200">
                    {{ $assign->subject->code }}
                </span>
            </div>
            @empty
            <p class="text-slate-400 py-4 col-span-2">No active teaching assignments.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
