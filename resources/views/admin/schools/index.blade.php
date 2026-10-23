@extends('layouts.app')

@section('title', 'Schools & Campuses Management')

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800 uppercase tracking-wider">
                    Multi-Campus Registry
                </span>
                <span class="text-xs text-slate-500 font-mono">{{ $totalSchools }} Registered Institutions</span>
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 mt-1">Schools & Branch Campuses</h1>
            <p class="text-xs text-slate-500">Manage institutional directory, campus hierarchy (parent & branch schools), and view all linked child records.</p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.schools.create') }}" class="text-xs font-semibold px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition flex items-center gap-1.5">
                <span>➕</span> Register New School
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center gap-2">
            <span>✓</span> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium flex items-center gap-2">
            <span>⚠️</span> {{ session('error') }}
        </div>
    @endif

    <!-- Metric Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Schools / Campuses</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $totalSchools }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Institutions in network</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="text-[11px] font-semibold text-indigo-600 uppercase tracking-wider">Total Enrolled Students</div>
            <div class="text-2xl font-black text-indigo-600 mt-1">{{ $totalStudents }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Across all campuses</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wider">Total Faculty & Staff</div>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ $totalStaff }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Teachers, accountants & admins</div>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.schools.index') }}" class="flex items-center gap-2 flex-1">
            <div class="flex-1 relative">
                <input type="text" name="q" value="{{ $search }}" placeholder="Search by school name, code, email, city..."
                       class="w-full text-xs rounded-xl border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 py-2 pl-3 pr-8">
                @if($search)
                    <a href="{{ route('admin.schools.index') }}" class="absolute right-2.5 top-2.5 text-xs text-slate-400 hover:text-slate-600 font-bold">✕</a>
                @endif
            </div>

            <button type="submit" class="text-xs font-semibold px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition">
                Search
            </button>
        </form>

        <span class="text-xs text-slate-500">
            Showing <strong>{{ $schools->count() }}</strong> of <strong>{{ $schools->total() }}</strong> schools
        </span>
    </div>

    <!-- Schools Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">School / Campus</th>
                        <th class="py-3 px-4">Type / Parent</th>
                        <th class="py-3 px-4">Enrolled Students</th>
                        <th class="py-3 px-4">Faculty & Staff</th>
                        <th class="py-3 px-4">Package Plan</th>
                        <th class="py-3 px-4">Contact</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($schools as $sc)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4">
                                <div class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
                                    <span class="w-7 h-7 rounded-lg bg-indigo-50 border border-indigo-200 text-indigo-700 font-bold flex items-center justify-center text-xs">
                                        🏫
                                    </span>
                                    <a href="{{ route('admin.schools.show', $sc) }}" class="hover:text-indigo-600 transition">
                                        {{ $sc->name }}
                                    </a>
                                </div>
                                <div class="text-[11px] font-mono text-slate-400 mt-0.5">Code: {{ $sc->code }}</div>
                            </td>

                            <td class="py-3 px-4">
                                @if($sc->parent)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                        ↳ Branch of {{ $sc->parent->name }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        🏢 Root School / Trust
                                    </span>
                                @endif

                                @if($sc->branches_count > 0)
                                    <div class="text-[10px] text-indigo-600 font-semibold mt-0.5">
                                        {{ $sc->branches_count }} Child {{ Str::plural('Branch', $sc->branches_count) }}
                                    </div>
                                @endif
                            </td>

                            <td class="py-3 px-4 font-bold text-slate-800">
                                <span class="px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200 text-[11px]">
                                    👥 {{ $sc->students_count }} Students
                                </span>
                            </td>

                            <td class="py-3 px-4 font-bold text-slate-800">
                                <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px]">
                                    👩‍🏫 {{ $sc->staff_count }} Staff
                                </span>
                            </td>

                            <td class="py-3 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-indigo-100 text-indigo-800">
                                    {{ $sc->package_tier }}
                                </span>
                                <div class="text-[10px] text-slate-500 font-mono mt-0.5">
                                    ₹ {{ number_format($sc->package_charge, 2) }}/{{ $sc->billing_cycle }}
                                </div>
                            </td>

                            <td class="py-3 px-4 text-slate-500">
                                <div>{{ $sc->phone ?? '—' }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $sc->email ?? '—' }}</div>
                            </td>

                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.schools.show', $sc) }}" class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 transition" title="View Related Students, Teachers, Accountants & Parents">
                                        👁 View Children
                                    </a>

                                    <a href="{{ route('admin.schools.edit', $sc) }}" class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                                        Edit
                                    </a>

                                    @if($totalSchools > 1)
                                    <form action="{{ route('admin.schools.destroy', $sc) }}" method="POST" onsubmit="return confirm('Delete school {{ $sc->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold px-2 py-1 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 border border-rose-200 transition">
                                            Delete
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="text-3xl mb-2">🏫</div>
                                <div class="font-bold text-slate-700">No Schools Found</div>
                                <div class="text-xs text-slate-400 mt-1">Click "Register New School" to add a campus.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($schools->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $schools->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
