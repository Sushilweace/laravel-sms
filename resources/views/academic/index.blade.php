@extends('layouts.app')

@section('title', 'Academic Setup')
@section('page-title', 'Academic Setup & Configuration')

@section('content')
<div class="space-y-8">

    <!-- 1. Academic Years Section -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
            <div>
                <h3 class="font-bold text-slate-900 text-base">Academic Years</h3>
                <p class="text-xs text-slate-500 mt-0.5">Exactly one year is active at any time. Locked years become read-only.</p>
            </div>
            <!-- Add Year Form Modal Trigger or Inline Form -->
            <form action="{{ route('academic.years.store') }}" method="POST" class="flex items-center gap-2 flex-wrap">
                @csrf
                <input type="text" name="name" placeholder="e.g. 2027-28" required class="text-xs px-3 py-1.5 rounded-lg border border-slate-300 w-28">
                <input type="date" name="starts_on" required class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-300">
                <input type="date" name="ends_on" required class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-300">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-xs px-3 py-1.5 rounded-lg transition">
                    + Add Year
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-y border-slate-200">
                    <tr>
                        <th class="py-2.5 px-4">Year Name</th>
                        <th class="py-2.5 px-4">Starts On</th>
                        <th class="py-2.5 px-4">Ends On</th>
                        <th class="py-2.5 px-4">Status</th>
                        <th class="py-2.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($years as $year)
                    <tr class="hover:bg-slate-50">
                        <td class="py-3 px-4 font-bold text-slate-900">{{ $year->name }}</td>
                        <td class="py-3 px-4 text-slate-600">{{ $year->starts_on->format('d M Y') }}</td>
                        <td class="py-3 px-4 text-slate-600">{{ $year->ends_on->format('d M Y') }}</td>
                        <td class="py-3 px-4">
                            @if($year->is_current)
                                <span class="bg-emerald-100 text-emerald-700 px-2.5 py-1 rounded-full font-semibold">Active Current</span>
                            @else
                                <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded">Inactive</span>
                            @endif

                            @if($year->is_locked)
                                <span class="bg-amber-100 text-amber-700 px-2 py-0.5 rounded ml-1 font-semibold">🔒 Locked</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right space-x-2">
                            @if(!$year->is_current)
                            <form action="{{ route('academic.years.set-current', $year) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-indigo-600 hover:text-indigo-800 font-semibold">Set Current</button>
                            </form>
                            @endif
                            @if(!$year->is_locked)
                            <form action="{{ route('academic.years.lock', $year) }}" method="POST" class="inline" onsubmit="return confirm('Locking this year makes all past attendance, marks and records read-only. Proceed?')">
                                @csrf
                                <button type="submit" class="text-amber-600 hover:text-amber-800 font-medium">Lock Year</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- 2. Classes & Sections Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Classes and Sections List -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-900 text-base">Classes & Sections ({{ $currentYear?->name }})</h3>
                <span class="text-xs text-slate-500">{{ $classes->count() }} classes</span>
            </div>

            <div class="divide-y divide-slate-100">
                @foreach($classes as $class)
                <div class="py-3.5 flex items-start justify-between flex-wrap gap-2">
                    <div>
                        <div class="font-bold text-sm text-slate-900">
                            {{ $class->name }}
                            <span class="text-xs font-normal text-slate-400 ml-1">Level {{ $class->numeric_level }}</span>
                            @if($class->nextClass)
                            <span class="text-[11px] text-emerald-600 ml-1.5">→ Promotes to {{ $class->nextClass->name }}</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                            <span class="text-xs text-slate-400">Sections:</span>
                            @forelse($class->sections as $section)
                            <span class="bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded text-xs font-semibold border border-indigo-100">
                                {{ $section->name }}
                                @if($section->classTeacher)
                                <span class="text-[10px] text-indigo-500 font-normal">({{ $section->classTeacher->first_name }})</span>
                                @endif
                            </span>
                            @empty
                            <span class="text-xs text-slate-400 italic">No sections</span>
                            @endforelse
                        </div>
                    </div>

                    <!-- Add Section Quick Form -->
                    <form action="{{ route('academic.sections.store') }}" method="POST" class="flex items-center gap-1.5">
                        @csrf
                        <input type="hidden" name="school_class_id" value="{{ $class->id }}">
                        <input type="hidden" name="academic_year_id" value="{{ $currentYear?->id }}">
                        <input type="text" name="name" placeholder="Sec (C)" required class="text-xs px-2 py-1 rounded border border-slate-300 w-16">
                        <select name="class_teacher_id" class="text-xs px-2 py-1 rounded border border-slate-300">
                            <option value="">Teacher (Optional)</option>
                            @foreach($staffMembers as $st)
                            <option value="{{ $st->id }}">{{ $st->first_name }} {{ $st->last_name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="bg-slate-100 hover:bg-indigo-50 text-slate-700 hover:text-indigo-600 font-medium text-xs px-2 py-1 rounded border border-slate-200 transition">
                            + Add
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Add Class Card -->
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h4 class="font-bold text-slate-900 text-sm mb-3">Add New Class</h4>
                <form action="{{ route('academic.classes.store') }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Class Name</label>
                        <input type="text" name="name" placeholder="e.g. Class 11" required class="w-full px-3 py-2 rounded-lg border border-slate-300">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Numeric Order Level</label>
                        <input type="number" name="numeric_level" placeholder="11" min="1" max="25" required class="w-full px-3 py-2 rounded-lg border border-slate-300">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Next Class (For Promotion)</label>
                        <select name="next_class_id" class="w-full px-3 py-2 rounded-lg border border-slate-300">
                            <option value="">None (Final Graduating Class)</option>
                            @foreach($classes as $cl)
                            <option value="{{ $cl->id }}">{{ $cl->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 rounded-lg transition">
                        Create Class
                    </button>
                </form>
            </div>

            <!-- Teaching Assignment Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h4 class="font-bold text-slate-900 text-sm mb-3">Assign Teacher to Subject</h4>
                <form action="{{ route('academic.assign-teacher') }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <input type="hidden" name="academic_year_id" value="{{ $currentYear?->id }}">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Section</label>
                        <select name="section_id" required class="w-full px-3 py-2 rounded-lg border border-slate-300">
                            @foreach($classes as $cl)
                                @foreach($cl->sections as $sec)
                                <option value="{{ $sec->id }}">{{ $cl->name }} - {{ $sec->name }}</option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Subject</label>
                        <select name="subject_id" required class="w-full px-3 py-2 rounded-lg border border-slate-300">
                            @foreach($subjects as $sb)
                            <option value="{{ $sb->id }}">{{ $sb->name }} ({{ $sb->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Faculty / Staff Member</label>
                        <select name="staff_id" required class="w-full px-3 py-2 rounded-lg border border-slate-300">
                            @foreach($staffMembers as $st)
                            <option value="{{ $st->id }}">{{ $st->first_name }} {{ $st->last_name }} ({{ $st->designation }})</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 rounded-lg transition">
                        Save Assignment
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- 3. Subjects Catalog -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
            <div>
                <h3 class="font-bold text-slate-900 text-base">Curriculum Subjects</h3>
                <p class="text-xs text-slate-500 mt-0.5">Global subjects taught across classes.</p>
            </div>
            <form action="{{ route('academic.subjects.store') }}" method="POST" class="flex items-center gap-2">
                @csrf
                <input type="text" name="name" placeholder="Subject Name" required class="text-xs px-3 py-1.5 rounded-lg border border-slate-300">
                <input type="text" name="code" placeholder="Code (e.g. BIO)" required class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-300 w-24">
                <select name="type" class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-300">
                    <option value="theory">Theory</option>
                    <option value="practical">Practical</option>
                    <option value="both" selected>Both</option>
                </select>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-xs px-3 py-1.5 rounded-lg transition">
                    + Add Subject
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
            @foreach($subjects as $sub)
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                <div class="text-xs font-bold text-slate-900">{{ $sub->name }}</div>
                <div class="text-[11px] text-slate-500 mt-1 flex items-center justify-between">
                    <span class="font-mono bg-white px-1.5 py-0.5 rounded border border-slate-200">{{ $sub->code }}</span>
                    <span class="capitalize">{{ $sub->type }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
