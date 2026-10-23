@extends('layouts.app')

@section('title', 'Add Staff Member')
@section('page-title', 'Create New Staff / Faculty Account')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
        <div class="border-b border-slate-100 pb-4 mb-6">
            <h3 class="font-bold text-slate-900 text-base">New Staff Registration</h3>
            <p class="text-xs text-slate-500 mt-1">Generates staff record and login credentials automatically.</p>
        </div>

        <form action="{{ route('staff.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">First Name *</label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" required
                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Last Name *</label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" required
                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Email Address *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="staff@school.test"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Mobile Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+91 99999 00000"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Designation *</label>
                    <input type="text" name="designation" value="{{ old('designation', 'Teacher') }}" required
                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">System Role *</label>
                    <select name="role" required class="w-full px-3 py-2 rounded-xl border border-slate-300">
                        <option value="Teacher">Teacher</option>
                        <option value="School Admin">School Admin / Principal</option>
                        <option value="Accountant">Accountant / Bursar</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Gender *</label>
                    <select name="gender" required class="w-full px-3 py-2 rounded-xl border border-slate-300">
                        <option value="female">Female</option>
                        <option value="male">Male</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Highest Qualification</label>
                    <input type="text" name="qualification" value="{{ old('qualification') }}" placeholder="e.g. M.Sc. Mathematics, B.Ed."
                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('staff.index') }}" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-600 font-semibold hover:bg-slate-50 transition">
                    Cancel
                </a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2 rounded-xl shadow-md transition">
                    Save Staff Member
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
