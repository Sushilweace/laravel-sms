@extends('layouts.app')

@section('title', 'Fee Invoices')
@section('page-title', 'Student Fee Invoices')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar with Generator Form -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between flex-wrap gap-4">
        <!-- Search and Filter -->
        <form action="{{ route('fees.invoices') }}" method="GET" class="flex items-center gap-2 flex-wrap flex-1 max-w-xl">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by student, invoice #..."
                class="flex-1 px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">

            <select name="status" onchange="this.form.submit()" class="text-xs px-3 py-2 rounded-xl border border-slate-300">
                <option value="">All Statuses</option>
                <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
            </select>

            <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white text-xs px-3.5 py-2 rounded-xl font-medium transition">
                Filter
            </button>
            @if(request()->hasAny(['search', 'status']))
            <a href="{{ route('fees.invoices') }}" class="text-xs text-slate-500 hover:text-slate-700 py-2">Clear</a>
            @endif
        </form>

        <!-- Generate Periodic Invoices Form -->
        @can('fees.generate')
        <form action="{{ route('fees.invoices.generate') }}" method="POST" class="flex items-center gap-2 text-xs">
            @csrf
            <input type="text" name="period_key" placeholder="Period Key (e.g. 2026-06)" required class="px-3 py-2 rounded-xl border border-slate-300 w-36">
            <input type="date" name="due_date" value="{{ now()->addDays(15)->toDateString() }}" required class="px-2.5 py-2 rounded-xl border border-slate-300">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-xl shadow-md transition">
                ⚡ Generate Monthly Invoices
            </button>
        </form>
        @endcan
    </div>

    <!-- Invoices Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Invoice #</th>
                        <th class="py-3 px-4">Student</th>
                        <th class="py-3 px-4">Class</th>
                        <th class="py-3 px-4">Period</th>
                        <th class="py-3 px-4">Due Date</th>
                        <th class="py-3 px-4 text-right">Net Amount</th>
                        <th class="py-3 px-4 text-right">Paid</th>
                        <th class="py-3 px-4 text-right">Balance Due</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($invoices as $inv)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ $inv->invoice_number }}</td>
                        <td class="py-3 px-4 font-bold text-slate-900">
                            {{ $inv->enrollment->student->full_name }}
                            <div class="text-[10px] text-slate-400 font-mono">{{ $inv->enrollment->student->admission_no }}</div>
                        </td>
                        <td class="py-3 px-4 text-slate-600">{{ $inv->enrollment->section->full_name }}</td>
                        <td class="py-3 px-4 font-mono text-slate-700">{{ $inv->period_key }}</td>
                        <td class="py-3 px-4 text-slate-600">{{ $inv->due_on->format('d M Y') }}</td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">₹{{ number_format($inv->net_total, 2) }}</td>
                        <td class="py-3 px-4 text-right font-mono text-emerald-600 font-bold">₹{{ number_format($inv->paid_total, 2) }}</td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">
                            @if(bccomp((string) $inv->balance_due, '0.00', 2) > 0)
                                <span class="text-rose-600">₹{{ number_format($inv->balance_due, 2) }}</span>
                            @else
                                <span class="text-emerald-600">₹0.00</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase
                                {{ $inv->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                {{ $inv->status === 'partial' ? 'bg-blue-100 text-blue-700' : '' }}
                                {{ $inv->status === 'open' ? 'bg-amber-100 text-amber-700' : '' }}
                                {{ $inv->status === 'overdue' ? 'bg-rose-100 text-rose-700' : '' }}">
                                {{ $inv->status }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right space-x-2">
                            @can('fees.collect')
                                @if($inv->status !== 'paid')
                                <a href="{{ route('fees.collect', $inv) }}" class="text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold px-2.5 py-1 rounded-lg border border-indigo-200 transition">
                                    Collect →
                                </a>
                                @endif
                            @endcan
                            @if($inv->payments->first())
                            <a href="{{ route('fees.receipt', $inv->payments->first()) }}" class="text-xs text-slate-600 hover:text-indigo-600 font-semibold">
                                Receipt →
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-8 text-center text-slate-500">No invoices found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $invoices->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
