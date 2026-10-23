@extends('layouts.app')

@section('title', 'Student Promotion')
@section('page-title', 'Year-End Student Promotion')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
        <div class="mb-6 border-b border-slate-100 pb-4">
            <h3 class="font-bold text-slate-900 text-base">Bulk Class Promotion</h3>
            <p class="text-xs text-slate-500 mt-1">
                Promote students from one section into the next class and section for the target academic year.
                Historical enrollments, attendance, and exam marks remain intact and untampered.
            </p>
        </div>

        <form action="{{ route('students.promote.submit') }}" method="POST" class="space-y-5 text-xs">
            @csrf

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Source Section (Current Students) *</label>
                <select name="from_section_id" required class="w-full px-3 py-2 rounded-xl border border-slate-300">
                    <option value="">Select Section to Promote</option>
                    @foreach($classes as $cl)
                        <optgroup label="{{ $cl->name }}">
                            @foreach($cl->sections as $sec)
                            <option value="{{ $sec->id }}">{{ $cl->name }} - {{ $sec->name }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Target Academic Year *</label>
                    <select name="to_academic_year_id" required class="w-full px-3 py-2 rounded-xl border border-slate-300">
                        @foreach($years as $yr)
                        <option value="{{ $yr->id }}" {{ $yr->is_current ? 'selected' : '' }}>{{ $yr->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Target Section *</label>
                    <select name="to_section_id" required class="w-full px-3 py-2 rounded-xl border border-slate-300">
                        <option value="">Select Destination Section</option>
                        @foreach($classes as $cl)
                            <optgroup label="{{ $cl->name }}">
                                @foreach($cl->sections as $sec)
                                <option value="{{ $sec->id }}">{{ $cl->name }} - {{ $sec->name }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="p-4 bg-amber-50 rounded-xl border border-amber-200 text-amber-900">
                <div class="font-bold flex items-center gap-1.5 mb-1">
                    <span>⚠</span> Promotion Safety Rules
                </div>
                <p class="text-[11px] leading-relaxed">
                    Promotion creates brand-new enrollment rows in the target year.
                    Past years remain fully queryable for reports and transcripts.
                </p>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3">
                <a href="{{ route('students.index') }}" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-600 font-semibold hover:bg-slate-50 transition">
                    Cancel
                </a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2 rounded-xl shadow-md transition">
                    Execute Bulk Promotion
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
