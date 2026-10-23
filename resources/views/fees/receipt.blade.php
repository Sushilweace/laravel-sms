@extends('layouts.app')

@section('title', 'Receipt ' . $payment->payment_number)
@section('page-title', 'Official Payment Receipt')

@section('content')
<div class="max-w-2xl mx-auto space-y-4">

    <!-- Action Toolbar -->
    <div class="flex items-center justify-between no-print">
        <a href="{{ route('fees.invoices') }}" class="text-xs text-slate-500 hover:text-indigo-600 font-medium">
            ← Return to Invoices
        </a>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-4 py-2 rounded-xl border border-slate-200 transition">
                🖨 Print Receipt
            </button>
            <a href="{{ request()->fullUrlWithQuery(['download' => '1']) }}" class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-xl shadow-md transition">
                ⬇ Download Official PDF
            </a>
        </div>
    </div>

    <!-- Official Receipt Sheet -->
    <div class="bg-white rounded-2xl border border-slate-300 shadow-xl p-8 sm:p-10 print:border-none print:shadow-none print:p-0">

        <!-- Header -->
        <div class="text-center border-b-2 border-slate-900 pb-5 mb-6">
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight uppercase">
                {{ $school->name ?? 'Greenwood International School' }}
            </h1>
            <p class="text-xs text-slate-600 mt-1">
                {{ $school->address ?? 'Knowledge City, Bangalore' }} • Phone: {{ $school->phone ?? '+91 98765 43210' }}
            </p>
            <div class="inline-block mt-3 px-3 py-1 rounded bg-slate-900 text-white text-xs font-bold uppercase tracking-wider">
                Official Fee Receipt
            </div>
        </div>

        <!-- Receipt Meta -->
        <div class="flex justify-between items-start text-xs border-b border-slate-200 pb-4 mb-4">
            <div>
                <span class="text-slate-400 font-semibold uppercase text-[10px]">Receipt Number</span>
                <div class="font-mono font-extrabold text-slate-900 text-sm mt-0.5">{{ $payment->payment_number }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Invoice: <strong class="font-mono text-slate-700">{{ $payment->invoice->invoice_number }}</strong></div>
            </div>
            <div class="text-right">
                <span class="text-slate-400 font-semibold uppercase text-[10px]">Date & Time</span>
                <div class="font-bold text-slate-900 text-sm mt-0.5">{{ $payment->paid_at->format('d M Y, h:i A') }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Payment Mode: <strong class="uppercase text-slate-700">{{ $payment->payment_method }}</strong></div>
            </div>
        </div>

        <!-- Student Details -->
        <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs mb-6 grid grid-cols-2 gap-3">
            <div>
                <span class="text-slate-400 font-semibold text-[10px] uppercase">Received From Student</span>
                <div class="font-bold text-slate-900 mt-0.5 text-sm">{{ $payment->invoice->enrollment->student->full_name }}</div>
                <div class="text-slate-500 text-[11px]">Admission No: {{ $payment->invoice->enrollment->student->admission_no }}</div>
            </div>
            <div>
                <span class="text-slate-400 font-semibold text-[10px] uppercase">Class & Section</span>
                <div class="font-bold text-slate-900 mt-0.5 text-sm">{{ $payment->invoice->enrollment->section->full_name }}</div>
                <div class="text-slate-500 text-[11px]">Billing Period: {{ $payment->invoice->period_key }}</div>
            </div>
        </div>

        <!-- Amount Box -->
        <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-200 text-center mb-6">
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-800">Amount Paid</span>
            <div class="text-3xl font-extrabold text-emerald-700 font-mono mt-1">
                ₹{{ number_format($payment->amount, 2) }}
            </div>
            @if($payment->transaction_reference)
            <div class="text-[11px] text-emerald-800 mt-1 font-mono">
                Transaction Reference: {{ $payment->transaction_reference }}
            </div>
            @endif
        </div>

        <!-- Signatures & Stamp -->
        <div class="pt-6 border-t border-slate-200 flex justify-between items-end text-xs">
            <div>
                <span class="px-2.5 py-1 bg-slate-100 rounded text-[10px] font-bold text-slate-600 font-mono">
                    STATUS: {{ strtoupper($payment->status) }}
                </span>
                <div class="text-[10px] text-slate-400 mt-1">Collected by: {{ $payment->collector->name }}</div>
            </div>
            <div class="text-center">
                <div class="h-8"></div>
                <div class="border-t border-slate-400 pt-1 font-semibold text-slate-700 w-36">
                    Authorized Cashier
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
