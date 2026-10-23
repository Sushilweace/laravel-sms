@extends('layouts.app')

@section('title', 'Staff Directory')
@section('page-title', 'Faculty & Staff Directory')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between flex-wrap gap-4">
        <form action="{{ route('staff.index') }}" method="GET" class="flex items-center gap-2 flex-1 max-w-md">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, employee code, designation..."
                class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white text-xs px-3.5 py-2 rounded-xl font-medium transition">
                Search
            </button>
        </form>

        @can('staff.manage')
        <a href="{{ route('staff.create') }}" class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-xl shadow-md transition">
            + Add Staff Member
        </a>
        @endcan
    </div>

    <!-- Staff Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Staff Member</th>
                        <th class="py-3 px-4">Employee Code</th>
                        <th class="py-3 px-4">Designation</th>
                        <th class="py-3 px-4">Role</th>
                        <th class="py-3 px-4">Phone</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($staff as $st)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-700 font-bold flex items-center justify-center text-xs">
                                    {{ substr($st->first_name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900">{{ $st->full_name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $st->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4 font-mono font-medium text-slate-600">{{ $st->employee_code }}</td>
                        <td class="py-3 px-4 font-medium text-slate-800">{{ $st->designation }}</td>
                        <td class="py-3 px-4">
                            <span class="bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded text-[11px] font-semibold border border-indigo-100">
                                {{ $st->user?->getRoleNames()->first() ?? 'Staff' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-slate-600 font-mono">{{ $st->phone ?? '—' }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold
                                {{ $st->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ ucfirst($st->status) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('staff.show', $st) }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold px-2 py-1 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition">
                                Profile →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500">No faculty members found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($staff->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $staff->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
