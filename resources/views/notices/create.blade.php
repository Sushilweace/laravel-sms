@extends('layouts.app')

@section('title', 'Compose Announcement')
@section('page-title', 'Compose School Notice')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
        <div class="border-b border-slate-100 pb-4 mb-6">
            <h3 class="font-bold text-slate-900 text-base">New Announcement</h3>
            <p class="text-xs text-slate-500 mt-1">Target announcement to all users, specific roles, or classes.</p>
        </div>

        <form action="{{ route('notices.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Notice Title *</label>
                <input type="text" name="title" value="{{ old('title') }}" required placeholder="e.g. Annual Sports Day Schedule"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Notice Content *</label>
                <textarea name="content" rows="6" required placeholder="Write announcement details..."
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 leading-relaxed">{{ old('content') }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Priority Level *</label>
                    <select name="priority" required class="w-full px-3 py-2 rounded-xl border border-slate-300">
                        <option value="normal">Normal</option>
                        <option value="important">Important</option>
                        <option value="emergency">Emergency Broadcast</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Target Audience *</label>
                    <select name="audience_type" id="audienceType" onchange="toggleAudience(this.value)" required class="w-full px-3 py-2 rounded-xl border border-slate-300">
                        <option value="all">Entire School (All Users)</option>
                        <option value="role">Specific User Role</option>
                        <option value="class">Specific Class</option>
                    </select>
                </div>
            </div>

            <!-- Role Selector (Hidden by default) -->
            <div id="roleSelector" class="hidden">
                <label class="block font-semibold text-slate-700 mb-1">Target Role</label>
                <select name="role" class="w-full px-3 py-2 rounded-xl border border-slate-300">
                    <option value="Parent">Parents / Guardians</option>
                    <option value="Teacher">Faculty / Teachers</option>
                    <option value="Student">Students Only</option>
                    <option value="Accountant">Accounts Staff</option>
                </select>
            </div>

            <!-- Class Selector (Hidden by default) -->
            <div id="classSelector" class="hidden">
                <label class="block font-semibold text-slate-700 mb-1">Target Class</label>
                <select name="class_id" class="w-full px-3 py-2 rounded-xl border border-slate-300">
                    @foreach($classes as $cl)
                    <option value="{{ $cl->id }}">{{ $cl->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-6 pt-2">
                <label class="flex items-center gap-2 cursor-pointer font-medium text-slate-700">
                    <input type="checkbox" name="is_pinned" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    📌 Pin to top of board
                </label>
                <label class="flex items-center gap-2 cursor-pointer font-medium text-slate-700">
                    <input type="checkbox" name="requires_ack" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    ✓ Require Parent/User Acknowledgement
                </label>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('notices.index') }}" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-600 font-semibold hover:bg-slate-50 transition">
                    Cancel
                </a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2 rounded-xl shadow-md transition">
                    Publish Announcement
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleAudience(val) {
        document.getElementById('roleSelector').classList.toggle('hidden', val !== 'role');
        document.getElementById('classSelector').classList.toggle('hidden', val !== 'class');
    }
</script>
@endsection
