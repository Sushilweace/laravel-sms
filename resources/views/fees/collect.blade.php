@extends('layouts.app')

@section('title', 'Collect Fee')
@section('page-title', 'Collect Payment: ' . $invoice->invoice_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('fees.invoices') }}" class="text-xs text-slate-500 hover:text-indigo-600 font-medium">
            ← Back to Invoices
        </a>
    </div>

    <!-- Invoice Header & Meta -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <span class="text-xs font-semibold text-slate-400 font-mono">{{ $invoice->invoice_number }}</span>
                <h2 class="text-xl font-bold text-slate-900 mt-0.5">
                    {{ $invoice->enrollment->student->full_name }}
                </h2>
                <div class="text-xs text-slate-500 mt-1 flex items-center gap-3">
                    <span>Admission: <strong>{{ $invoice->enrollment->student->admission_no }}</strong></span>
                    <span>•</span>
                    <span>Class: <strong>{{ $invoice->enrollment->section->full_name }}</strong></span>
                    <span>•</span>
                    <span>Period: <strong class="font-mono">{{ $invoice->period_key }}</strong></span>
                </div>
            </div>

            <!-- Balances -->
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 text-right text-xs">
                <div class="text-slate-400 font-semibold uppercase text-[10px]">Balance Due</div>
                <div class="text-2xl font-extrabold text-rose-600 font-mono mt-0.5">
                    ₹{{ number_format($invoice->balance_due, 2) }}
                </div>
                <div class="text-[11px] text-slate-500 mt-1">
                    Net: ₹{{ number_format($invoice->net_total, 2) }} | Paid: ₹{{ number_format($invoice->paid_total, 2) }}
                </div>
            </div>
        </div>

        <!-- Line items table -->
        <div class="mt-6 border-t border-slate-100 pt-4">
            <h4 class="font-bold text-slate-800 text-xs uppercase tracking-wider mb-2">Invoice Fee Heads</h4>
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-y border-slate-200">
                    <tr>
                        <th class="py-2 px-3">Fee Head</th>
                        <th class="py-2 px-3 text-right">Standard Amount</th>
                        <th class="py-2 px-3 text-right">Concession Discount</th>
                        <th class="py-2 px-3 text-right">Net Head Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @foreach($invoice->items as $item)
                    <tr>
                        <td class="py-2 px-3 font-sans font-bold text-slate-800">{{ $item->feeHead->name }}</td>
                        <td class="py-2 px-3 text-right text-slate-600">₹{{ number_format($item->amount, 2) }}</td>
                        <td class="py-2 px-3 text-right text-emerald-600">-₹{{ number_format($item->discount_amount, 2) }}</td>
                        <td class="py-2 px-3 text-right font-bold text-slate-900">₹{{ number_format($item->net_total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Payment Collection Form -->
    @if(bccomp((string) $invoice->balance_due, '0.00', 2) > 0)
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <h3 class="font-bold text-slate-900 text-sm mb-4">Record Payment & Issue Receipt</h3>
        <form action="{{ route('fees.collect.submit', $invoice) }}" method="POST" class="space-y-4 text-xs">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Amount to Pay (₹) *</label>
                    <input type="number" step="0.01" min="1" max="{{ $invoice->balance_due }}"
                        name="amount" value="{{ $invoice->balance_due }}" required
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 font-mono font-bold text-base text-slate-900">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Payment Method *</label>
                    <select name="payment_method" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300">
                        <option value="cash">Cash</option>
                        <option value="upi" selected>UPI (GPay / PhonePe / Paytm)</option>
                        <option value="card">Debit / Credit Card</option>
                        <option value="bank">Bank Transfer (NEFT/IMPS)</option>
                        <option value="cheque">Cheque</option>
                        <option value="online">Online Gateway</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Transaction Ref / Cheque #</label>
                    <input type="text" name="reference" placeholder="e.g. UPI-984214"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Notes / Remarks</label>
                <input type="text" name="notes" placeholder="Optional notes for receipt..."
                    class="w-full px-3 py-2 rounded-xl border border-slate-300">
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl shadow-lg shadow-indigo-600/30 transition text-xs">
                    Confirm Payment & Generate Receipt
                </button>
            </div>
        </form>
    </div>
    @endif

    <!-- Payment History on This Invoice -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <h3 class="font-bold text-slate-900 text-sm mb-4">Payment Transactions & Receipts</h3>
        <div class="divide-y divide-slate-100 text-xs">
            @forelse($invoice->payments as $payment)
            <div class="py-3 flex items-center justify-between flex-wrap gap-2">
                <div>
                    <div class="font-mono font-bold text-slate-900">{{ $payment->payment_number }}</div>
                    <div class="text-slate-500 mt-0.5">
                        {{ $payment->paid_at->format('d M Y, h:i A') }} • Mode: <span class="uppercase font-semibold">{{ $payment->payment_method }}</span>
                        @if($payment->transaction_reference)
                        • Ref: <span class="font-mono">{{ $payment->transaction_reference }}</span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <span class="font-mono font-bold text-base {{ $payment->isReversed() ? 'line-through text-slate-400' : 'text-emerald-600' }}">
                        ₹{{ number_format($payment->amount, 2) }}
                    </span>

                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                        {{ $payment->isCompleted() ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                        {{ $payment->status }}
                    </span>

                    <a href="{{ route('fees.receipt', $payment) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold px-2 py-1 bg-indigo-50 rounded border border-indigo-200">
                        Receipt →
                    </a>

                    @if($payment->isCompleted())
                    <form action="{{ route('fees.payments.reverse', $payment) }}" method="POST" class="inline"
                        onsubmit="return confirm('Reverse this payment? This will restore the invoice balance and mark receipt cancelled.')">
                        @csrf
                        <input type="hidden" name="reason" value="Payment reversed by authorized user">
                        <button type="submit" class="text-rose-600 hover:text-rose-800 text-xs underline">
                            Reverse
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @empty
            <p class="text-slate-400 py-3">No payments received for this invoice yet.</p>
            @endforelse
        </div>
    </div>

</div>
@endsection
