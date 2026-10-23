@extends('layouts.app')

@section('title', 'Student Admission')
@section('page-title', 'New Student Admission Form')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
        <div class="border-b border-slate-100 pb-4 mb-6 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-900 text-lg">Student Admission Application</h3>
                <p class="text-xs text-slate-500 mt-1">Enroll a new student into the active academic year.</p>
            </div>
            <a href="{{ route('students.index') }}" class="text-xs text-slate-500 hover:text-slate-800">Cancel & return</a>
        </div>

        <form action="{{ route('students.store') }}" method="POST" class="space-y-8">
            @csrf

            <!-- 1. Student Personal Details -->
            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-600 mb-4 flex items-center gap-2">
                    <span class="w-5 h-5 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-[10px]">1</span>
                    Student Personal Information
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">First Name *</label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" required
                            class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Last Name *</label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" required
                            class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Gender *</label>
                        <select name="gender" required class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Date of Birth *</label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" required
                            class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Blood Group</label>
                        <select name="blood_group" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Unknown</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Custom Admission No (Optional)</label>
                        <input type="text" name="admission_no" value="{{ old('admission_no') }}" placeholder="Auto-generated if blank"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="mt-4 text-xs">
                    <label class="block font-semibold text-slate-700 mb-1">Residential Address</label>
                    <textarea name="address" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('address') }}</textarea>
                </div>
            </div>

            <!-- 2. Academic Class & Section Assignment -->
            <div class="pt-4 border-t border-slate-100">
                <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-600 mb-4 flex items-center gap-2">
                    <span class="w-5 h-5 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-[10px]">2</span>
                    Class & Section Placement
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Assign Class & Section *</label>
                        <select name="section_id" required class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select Class & Section</option>
                            @foreach($classes as $cl)
                                <optgroup label="{{ $cl->name }}">
                                    @foreach($cl->sections as $sec)
                                    <option value="{{ $sec->id }}" {{ old('section_id') == $sec->id ? 'selected' : '' }}>
                                        {{ $cl->name }} - Section {{ $sec->name }}
                                    </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Roll Number</label>
                        <input type="number" name="roll_number" value="{{ old('roll_number') }}" placeholder="Optional roll number"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <!-- 3. Guardian & Family Details -->
            <div class="pt-4 border-t border-slate-100">
                <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-600 mb-4 flex items-center gap-2">
                    <span class="w-5 h-5 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-[10px]">3</span>
                    Primary Guardian & Contact
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Guardian Full Name *</label>
                        <input type="text" name="guardian_name" value="{{ old('guardian_name') }}" required placeholder="e.g. Ramesh Kumar"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Relationship *</label>
                        <select name="guardian_relation" required class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="Father">Father</option>
                            <option value="Mother">Mother</option>
                            <option value="Guardian">Legal Guardian</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Mobile Phone (For Alerts & Login) *</label>
                        <input type="text" name="guardian_phone" value="{{ old('guardian_phone') }}" required placeholder="+91 98765 00000"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Email Address</label>
                        <input type="email" name="guardian_email" value="{{ old('guardian_email') }}" placeholder="guardian@email.test"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('students.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50 transition">
                    Cancel
                </a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs px-6 py-2.5 rounded-xl shadow-lg shadow-indigo-600/30 transition">
                    Complete Admission
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
