@extends('layouts.app')

@section('title', 'Fees & Structures')
@section('page-title', 'Fee Structures & Financial Overview')

@section('content')
<div class="space-y-6">

    <!-- KPI Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Invoiced ({{ $currentYear?->name }})</span>
            <h3 class="text-2xl font-bold text-slate-900 mt-1">₹{{ number_format($stats['totalInvoiced']) }}</h3>
            <p class="text-xs text-slate-400 mt-0.5">All generated invoices</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Collected</span>
            <h3 class="text-2xl font-bold text-emerald-600 mt-1">₹{{ number_format($stats['totalCollected']) }}</h3>
            <p class="text-xs text-emerald-600 font-medium mt-0.5">Completed payments</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Outstanding Dues</span>
            <h3 class="text-2xl font-bold text-amber-600 mt-1">₹{{ number_format($stats['totalPending']) }}</h3>
            <p class="text-xs text-amber-600 font-medium mt-0.5">
                <a href="{{ route('fees.defaulters') }}" class="underline font-bold">View Overdue Defaulters →</a>
            </p>
        </div>
    </div>

    <!-- Quick Navigation Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-2">
            <a href="{{ route('fees.invoices') }}" class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-xl shadow-md transition">
                📄 View Invoices Directory
            </a>
            <a href="{{ route('fees.defaulters') }}" class="text-xs bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold px-4 py-2 rounded-xl border border-rose-200 transition">
                ⚠ Defaulters List
            </a>
        </div>
    </div>

    <!-- 2-Column Fee Heads & Class Structures -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Fee Heads Management -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 lg:col-span-1 space-y-4">
            <div>
                <h3 class="font-bold text-slate-900 text-base">Fee Heads Catalog</h3>
                <p class="text-xs text-slate-500 mt-0.5">Tuition, Transport, Lab, Library, etc.</p>
            </div>

            <!-- Add Fee Head Form -->
            <form action="{{ route('fees.heads.store') }}" method="POST" class="space-y-3 text-xs bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Fee Head Name *</label>
                    <input type="text" name="name" placeholder="e.g. Annual Activity Fee" required class="w-full px-3 py-1.5 rounded-lg border border-slate-300">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Unique Code *</label>
                    <input type="text" name="code" placeholder="ACT" required class="w-full px-3 py-1.5 rounded-lg border border-slate-300">
                </div>
                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-medium py-1.5 rounded-lg transition">
                    + Add Fee Head
                </button>
            </form>

            <div class="divide-y divide-slate-100 text-xs">
                @foreach($feeHeads as $head)
                <div class="py-2.5 flex items-center justify-between">
                    <div>
                        <div class="font-bold text-slate-900">{{ $head->name }}</div>
                        <div class="text-[10px] text-slate-400 font-mono">{{ $head->code }}</div>
                    </div>
                    <span class="text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded">
                        {{ $head->is_refundable ? 'Refundable' : 'Standard' }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Class Fee Structures -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Class Fee Structures</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Assigned fee head amounts per academic class.</p>
                </div>

                <!-- Add/Update Structure Form -->
                <form action="{{ route('fees.structures.store') }}" method="POST" class="flex items-center gap-1.5 flex-wrap text-xs">
                    @csrf
                    <select name="school_class_id" required class="px-2 py-1.5 rounded-lg border border-slate-300">
                        @foreach($classes as $cl)
                        <option value="{{ $cl->id }}">{{ $cl->name }}</option>
                        @endforeach
                    </select>

                    <select name="fee_head_id" required class="px-2 py-1.5 rounded-lg border border-slate-300">
                        @foreach($feeHeads as $fh)
                        <option value="{{ $fh->id }}">{{ $fh->name }}</option>
                        @endforeach
                    </select>

                    <input type="number" step="0.01" name="amount" placeholder="Amount (₹)" required class="w-24 px-2 py-1.5 rounded-lg border border-slate-300">

                    <select name="frequency" class="px-2 py-1.5 rounded-lg border border-slate-300">
                        <option value="monthly">Monthly</option>
                        <option value="quarterly">Quarterly</option>
                        <option value="yearly">Yearly</option>
                        <option value="once">Once</option>
                    </select>

                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-3 py-1.5 rounded-lg transition">
                        Save
                    </button>
                </form>
            </div>

            <!-- Existing Structures Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-y border-slate-200">
                        <tr>
                            <th class="py-2.5 px-4">Class</th>
                            <th class="py-2.5 px-4">Fee Head</th>
                            <th class="py-2.5 px-4">Frequency</th>
                            <th class="py-2.5 px-4 text-right">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($structures as $st)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-2.5 px-4 font-bold text-slate-900">{{ $st->schoolClass->name }}</td>
                            <td class="py-2.5 px-4 text-slate-700">{{ $st->feeHead->name }}</td>
                            <td class="py-2.5 px-4 capitalize text-slate-500">{{ $st->frequency }}</td>
                            <td class="py-2.5 px-4 text-right font-mono font-bold text-slate-900">₹{{ number_format($st->amount, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-slate-400">No fee structures configured.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
