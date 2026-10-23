@extends('layouts.app')

@section('title', 'System Error Logs - Admin Panel')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 uppercase tracking-wider">
                    Diagnostics
                </span>
                <span class="text-xs text-slate-500 font-mono">storage/logs/laravel.log ({{ $fileSizeKb }} KB)</span>
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 mt-1">System Error & Exception Logs</h1>
            <p class="text-xs text-slate-500">Real-time log viewer with stack traces and exception diagnostics.</p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.logs.errors.download') }}" class="text-xs font-semibold px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 transition flex items-center gap-1.5 shadow-sm">
                <span>⬇</span> Download Log File
            </a>

            <form action="{{ route('admin.logs.errors.clear') }}" method="POST" onsubmit="return confirm('Purge and clear all contents of laravel.log?')">
                @csrf
                <button type="submit" class="text-xs font-semibold px-3.5 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition flex items-center gap-1.5 shadow-sm">
                    <span>🗑</span> Clear Log
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center gap-2">
            <span>✓</span> {{ session('success') }}
        </div>
    @endif

    <!-- Metric Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Recorded</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $totalCount }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Entries parsed</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="text-[11px] font-semibold text-rose-600 uppercase tracking-wider">Errors & Failures</div>
            <div class="text-2xl font-black text-rose-600 mt-1">{{ $errorCount }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Exceptions caught</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="text-[11px] font-semibold text-amber-600 uppercase tracking-wider">Critical Alerts</div>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ $criticalCount }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">High severity issues</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="text-[11px] font-semibold text-indigo-600 uppercase tracking-wider">Today's Events</div>
            <div class="text-2xl font-black text-indigo-600 mt-1">{{ $todayCount }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Logged today</div>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.logs.errors') }}" class="flex flex-wrap items-center gap-2 flex-1">
            <div class="flex items-center gap-1.5">
                <span class="text-xs font-semibold text-slate-600">Severity:</span>
                <select name="level" onchange="this.form.submit()" class="text-xs font-semibold rounded-xl border-slate-300 py-1.5 focus:ring-indigo-500 focus:border-indigo-500">
                    @foreach($levels as $lvl)
                        <option value="{{ $lvl }}" {{ $selectedLevel === $lvl ? 'selected' : '' }}>
                            {{ $lvl }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex-1 min-w-[200px] relative">
                <input type="text" name="q" value="{{ $search }}" placeholder="Search error class, message, or timestamp..."
                       class="w-full text-xs rounded-xl border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 py-1.5 pl-3 pr-8">
                @if($search)
                    <a href="{{ route('admin.logs.errors', ['level' => $selectedLevel]) }}" class="absolute right-2.5 top-1.5 text-xs text-slate-400 hover:text-slate-600 font-bold">✕</a>
                @endif
            </div>

            <button type="submit" class="text-xs font-semibold px-3 py-1.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition">
                Filter
            </button>
        </form>

        <span class="text-xs text-slate-500">
            Showing <strong>{{ count($entries) }}</strong> entries
        </span>
    </div>

    <!-- Log Entries List -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="divide-y divide-slate-100">
            @forelse($entries as $entry)
                <div class="p-4 hover:bg-slate-50/80 transition" x-data="{ expanded: false }">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider
                                {{ in_array($entry['level'], ['CRITICAL', 'EMERGENCY', 'ALERT']) ? 'bg-rose-600 text-white' : '' }}
                                {{ $entry['level'] === 'ERROR' ? 'bg-rose-100 text-rose-800 border border-rose-200' : '' }}
                                {{ $entry['level'] === 'WARNING' ? 'bg-amber-100 text-amber-800 border border-amber-200' : '' }}
                                {{ $entry['level'] === 'INFO' ? 'bg-indigo-100 text-indigo-800 border border-indigo-200' : '' }}
                                {{ $entry['level'] === 'DEBUG' ? 'bg-slate-100 text-slate-700 border border-slate-200' : '' }}">
                                {{ $entry['level'] }}
                            </span>

                            <span class="text-xs font-mono text-slate-500">
                                {{ $entry['timestamp'] }}
                            </span>

                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 uppercase font-mono">
                                {{ $entry['env'] }}
                            </span>
                        </div>

                        @if(!empty($entry['stack']))
                            <button @click="expanded = !expanded" 
                                    class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold inline-flex items-center gap-1 self-start sm:self-auto">
                                <span x-text="expanded ? 'Hide Stack Trace' : 'View Stack Trace'">View Stack Trace</span>
                                <span x-text="expanded ? '▲' : '▼'" class="text-[10px]">▼</span>
                            </button>
                        @endif
                    </div>

                    <div class="mt-2 text-xs font-mono text-slate-800 break-words leading-relaxed">
                        {{ $entry['message'] }}
                    </div>

                    @if(!empty($entry['stack']))
                        <div x-show="expanded" x-cloak class="mt-3 p-3 bg-slate-900 text-slate-200 rounded-xl font-mono text-[11px] overflow-x-auto max-h-96 leading-tight border border-slate-800 shadow-inner">
                            <pre class="whitespace-pre-wrap">{{ $entry['stack'] }}</pre>
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-12 text-center text-slate-400">
                    <div class="text-3xl mb-2">🎉</div>
                    <div class="font-bold text-slate-700">No Log Entries Found</div>
                    <div class="text-xs text-slate-400 mt-1">No errors matching the filter criteria were found in the log file.</div>
                </div>
            @endforelse
        </div>
    </div>

</div>

<!-- Alpine.js minimal helper for accordion toggle if Alpine is loaded, or fallback -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Alpine === 'undefined') {
        // Vanilla fallback for accordion
        document.querySelectorAll('[x-data]').forEach(el => {
            const btn = el.querySelector('button');
            const pre = el.querySelector('div[x-show]');
            if (btn && pre) {
                pre.style.display = 'none';
                btn.addEventListener('click', function () {
                    const isHidden = pre.style.display === 'none';
                    pre.style.display = isHidden ? 'block' : 'none';
                    btn.innerHTML = (isHidden ? 'Hide Stack Trace ▲' : 'View Stack Trace ▼');
                });
            }
        });
    }
});
</script>
@endsection
