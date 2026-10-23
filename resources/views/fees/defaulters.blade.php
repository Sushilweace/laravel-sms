@extends('layouts.app')

@section('title', 'Fee Defaulters')
@section('page-title', 'Outstanding Fee Defaulters List')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h3 class="font-bold text-slate-900 text-sm">Overdue Fee Accounts ({{ $currentYear?->name }})</h3>
            <p class="text-xs text-slate-500 mt-0.5">Students with past-due unpaid invoice balances.</p>
        </div>
        <a href="{{ route('fees.invoices') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold">
            ← Return to Invoices
        </a>
    </div>

    <!-- Defaulters Table -->
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
                        <th class="py-3 px-4">Days Overdue</th>
                        <th class="py-3 px-4 text-right">Balance Due</th>
                        <th class="py-3 px-4">Primary Guardian</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($overdueInvoices as $inv)
                    @php
                        $daysOverdue = max(0, (int) now()->diffInDays($inv->due_on, false) * -1);
                        $primaryGuardian = $inv->enrollment->student->guardians->first();
                    @endphp
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ $inv->invoice_number }}</td>
                        <td class="py-3 px-4 font-bold text-slate-900">
                            {{ $inv->enrollment->student->full_name }}
                            <div class="text-[10px] text-slate-400 font-mono">{{ $inv->enrollment->student->admission_no }}</div>
                        </td>
                        <td class="py-3 px-4 text-slate-600">{{ $inv->enrollment->section->full_name }}</td>
                        <td class="py-3 px-4 font-mono text-slate-700">{{ $inv->period_key }}</td>
                        <td class="py-3 px-4 text-slate-600">{{ $inv->due_on->format('d M Y') }}</td>
                        <td class="py-3 px-4">
                            <span class="bg-rose-100 text-rose-700 font-bold px-2 py-0.5 rounded text-[11px]">
                                {{ $daysOverdue }} days
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-extrabold text-rose-600">
                            ₹{{ number_format($inv->balance_due, 2) }}
                        </td>
                        <td class="py-3 px-4 text-slate-700">
                            @if($primaryGuardian)
                            <div class="font-medium">{{ $primaryGuardian->full_name }}</div>
                            <div class="font-mono text-indigo-600 text-[11px]">{{ $primaryGuardian->phone }}</div>
                            @else
                            <span class="text-slate-400 italic">None</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('fees.collect', $inv) }}" class="text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold px-2.5 py-1 rounded-lg border border-indigo-200 transition">
                                Collect →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-8 text-center text-slate-500">
                            No overdue defaulters found. All student fee dues are currently up to date!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
