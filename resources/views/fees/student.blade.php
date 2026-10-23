@extends('layouts.app')

@section('title', 'My Fees & Payment Receipts')

@section('content')
<div class="space-y-6 max-w-6xl mx-auto">

    <!-- Header Banner -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-black text-2xl shadow-lg shadow-indigo-600/30">
                {{ substr($student?->first_name ?? auth()->user()->name, 0, 1) }}
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800 uppercase tracking-wider">
                        Student Fee Account
                    </span>
                    <span class="text-xs text-slate-500 font-mono">Academic Year: {{ $currentYear?->name ?? '2026-27' }}</span>
                </div>
                <h1 class="text-xl font-extrabold text-slate-900 mt-1">
                    {{ $student?->full_name ?? auth()->user()->name }}
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    Admission No: <strong class="font-mono text-slate-700">{{ $student?->admission_no ?? 'ADM-2026' }}</strong>
                    @if($student?->currentEnrollment)
                        • {{ $student->currentEnrollment->section?->full_name }} (Roll #{{ $student->currentEnrollment->roll_number ?? '1' }})
                    @endif
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <span class="text-xs text-slate-500">
                School: <strong class="text-slate-800">{{ $currentSchool->name ?? 'Greenwood High' }}</strong>
            </span>
        </div>
    </div>

    <!-- Personal Overdue Status Alert -->
    @if($studentStats['overdueAmount'] > 0)
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 flex items-start gap-3 shadow-sm">
            <div class="text-2xl">⚠️</div>
            <div class="flex-1">
                <div class="text-sm font-bold text-rose-900">
                    Overdue Payment Pending: ₹ {{ number_format($studentStats['overdueAmount'], 2) }}
                </div>
                <p class="text-xs text-rose-700 mt-0.5">
                    You have <strong>{{ $studentStats['overdueCount'] }}</strong> invoice(s) past the scheduled due date. Please arrange fee settlement at the front-desk bursar office or online to avoid administrative late charges.
                </p>
            </div>
        </div>
    @elseif($studentStats['balanceDue'] == 0 && $studentStats['totalInvoiced'] > 0)
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-center gap-3 shadow-sm">
            <div class="text-2xl">✓</div>
            <div>
                <div class="text-sm font-bold text-emerald-900">All Dues Cleared!</div>
                <p class="text-xs text-emerald-700">All invoices for the current term have been settled in full. Thank you for timely payments.</p>
            </div>
        </div>
    @endif

    <!-- Personal Financial Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Billed ({{ $currentYear?->name }})</div>
            <div class="text-2xl font-black text-slate-900 mt-1">₹ {{ number_format($studentStats['totalInvoiced'], 2) }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">{{ $invoices->count() }} invoice(s) issued</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wider">Total Paid to Date</div>
            <div class="text-2xl font-black text-emerald-600 mt-1">₹ {{ number_format($studentStats['totalPaid'], 2) }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">{{ $payments->count() }} completed payment(s)</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="text-[11px] font-semibold text-amber-600 uppercase tracking-wider">Remaining Balance Due</div>
            <div class="text-2xl font-black {{ $studentStats['balanceDue'] > 0 ? 'text-amber-600' : 'text-slate-900' }} mt-1">
                ₹ {{ number_format($studentStats['balanceDue'], 2) }}
            </div>
            <div class="text-[10px] {{ $studentStats['overdueAmount'] > 0 ? 'text-rose-600 font-bold' : 'text-slate-400' }} mt-0.5">
                @if($studentStats['overdueAmount'] > 0)
                    ● Includes ₹ {{ number_format($studentStats['overdueAmount'], 2) }} overdue
                @else
                    No past-due charges
                @endif
            </div>
        </div>
    </div>

    <!-- Invoices & Dues Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Fee Invoices & Scheduled Dues</h2>
                <p class="text-xs text-slate-500">Detailed fee statements generated for this academic term.</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg">
                {{ $invoices->count() }} Invoices
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Invoice #</th>
                        <th class="py-3 px-4">Period / Term</th>
                        <th class="py-3 px-4">Due Date</th>
                        <th class="py-3 px-4">Fee Breakdown</th>
                        <th class="py-3 px-4 text-right">Total Billed</th>
                        <th class="py-3 px-4 text-right">Paid</th>
                        <th class="py-3 px-4 text-right">Balance Due</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($invoices as $inv)
                        @php
                            $isOverdue = $inv->status !== 'paid' && $inv->due_on && $inv->due_on < now()->toDateString() && (float) $inv->balance_due > 0;
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                {{ $inv->invoice_number }}
                            </td>

                            <td class="py-3 px-4 text-slate-800 font-semibold">
                                {{ ucfirst(str_replace('_', ' ', $inv->period_key)) }}
                            </td>

                            <td class="py-3 px-4">
                                @if($isOverdue)
                                    <span class="font-bold text-rose-600 flex items-center gap-1 font-mono">
                                        <span>⚠️</span> {{ \Carbon\Carbon::parse($inv->due_on)->format('d M Y') }}
                                    </span>
                                @else
                                    <span class="text-slate-600 font-mono">
                                        {{ $inv->due_on ? \Carbon\Carbon::parse($inv->due_on)->format('d M Y') : '—' }}
                                    </span>
                                @endif
                            </td>

                            <td class="py-3 px-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($inv->items as $it)
                                        <span class="px-1.5 py-0.5 rounded bg-slate-100 text-[10px] text-slate-600 border border-slate-200">
                                            {{ $it->feeHead->name ?? 'Fee' }}: ₹ {{ number_format($it->amount, 0) }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>

                            <td class="py-3 px-4 text-right font-mono font-semibold text-slate-800">
                                ₹ {{ number_format($inv->total_amount, 2) }}
                            </td>

                            <td class="py-3 px-4 text-right font-mono font-semibold text-emerald-600">
                                ₹ {{ number_format($inv->paid_total, 2) }}
                            </td>

                            <td class="py-3 px-4 text-right font-mono font-bold {{ (float)$inv->balance_due > 0 ? ($isOverdue ? 'text-rose-600' : 'text-amber-600') : 'text-slate-400' }}">
                                ₹ {{ number_format($inv->balance_due, 2) }}
                            </td>

                            <td class="py-3 px-4 text-center">
                                @if($inv->status === 'paid')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase tracking-wider">Paid</span>
                                @elseif($isOverdue)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 uppercase tracking-wider animate-pulse">Overdue</span>
                                @elseif($inv->status === 'partial')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 uppercase tracking-wider">Partial</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800 uppercase tracking-wider">Open</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">
                                No fee invoices generated for this student yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Payment Receipts & Transaction History -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Official Payment Receipts</h2>
                <p class="text-xs text-slate-500">Records of successfully completed fee payments with download links.</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg">
                {{ $payments->count() }} Payments
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Receipt #</th>
                        <th class="py-3 px-4">Payment Date</th>
                        <th class="py-3 px-4">Invoice Linked</th>
                        <th class="py-3 px-4">Payment Method</th>
                        <th class="py-3 px-4">Reference #</th>
                        <th class="py-3 px-4 text-right">Amount Paid</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payments as $pay)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-bold text-indigo-700">
                                {{ $pay->payment_number }}
                            </td>

                            <td class="py-3 px-4 text-slate-600 font-mono">
                                {{ $pay->created_at->format('d M Y, h:i A') }}
                            </td>

                            <td class="py-3 px-4 font-mono text-slate-700">
                                {{ $pay->invoice->invoice_number ?? '—' }}
                            </td>

                            <td class="py-3 px-4 text-slate-800 font-semibold uppercase">
                                {{ $pay->payment_method }}
                            </td>

                            <td class="py-3 px-4 font-mono text-slate-500 text-[11px]">
                                {{ $pay->reference ?? '—' }}
                            </td>

                            <td class="py-3 px-4 text-right font-mono font-bold text-emerald-700 text-sm">
                                ₹ {{ number_format($pay->amount, 2) }}
                            </td>

                            <td class="py-3 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase tracking-wider">
                                    {{ $pay->status }}
                                </span>
                            </td>

                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('fees.receipt', [$pay, 'download' => 1]) }}"
                                   class="text-xs font-semibold px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 transition inline-flex items-center gap-1">
                                    <span>⬇</span> Receipt (PDF)
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">
                                No payment receipts found. Payments recorded by the cashier will appear here immediately.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
