@extends('layouts.app')

@section('title', 'Create Examination')
@section('page-title', 'Define New Examination')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
        <div class="border-b border-slate-100 pb-4 mb-6">
            <h3 class="font-bold text-slate-900 text-base">Examination Details</h3>
            <p class="text-xs text-slate-500 mt-1">Set up exam terms, passing thresholds, grading scale and participating classes.</p>
        </div>

        <form action="{{ route('exams.store') }}" method="POST" class="space-y-5 text-xs">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Exam Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Mid-Term Assessment 2026" required
                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Term / Session</label>
                    <input type="text" name="term" value="{{ old('term') }}" placeholder="e.g. Term 1, Annual"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Start Date *</label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" required
                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">End Date *</label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}" required
                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Pass Percentage (%) *</label>
                    <input type="number" step="0.01" name="pass_percentage" value="{{ old('pass_percentage', 33.00) }}" required
                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Weightage in Annual %</label>
                    <input type="number" step="0.01" name="weightage" value="{{ old('weightage', 50.00) }}"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Grading Scale *</label>
                    <select name="grade_scale_id" required class="w-full px-3 py-2 rounded-xl border border-slate-300">
                        @foreach($gradeScales as $scale)
                        <option value="{{ $scale->id }}">{{ $scale->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-2">Include Participating Classes *</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 bg-slate-50 p-4 rounded-xl border border-slate-200">
                    @foreach($classes as $cl)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="class_ids[]" value="{{ $cl->id }}" checked
                            class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-slate-800 font-medium">{{ $cl->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('exams.index') }}" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-600 font-semibold hover:bg-slate-50 transition">
                    Cancel
                </a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2 rounded-xl shadow-md transition">
                    Create Examination
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
