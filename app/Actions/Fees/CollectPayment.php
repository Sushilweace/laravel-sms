<?php

namespace App\Actions\Fees;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CollectPayment
{
    public function execute(Invoice $invoice, string|float $amount, string $paymentMethod, User $collector, ?string $reference = null, ?string $notes = null): Payment
    {
        $amountStr = (string) number_format((float) $amount, 2, '.', '');

        if (Money::lte($amountStr, '0.00')) {
            throw new InvalidArgumentException('Payment amount must be greater than zero.');
        }

        return DB::transaction(function () use ($invoice, $amountStr, $paymentMethod, $collector, $reference, $notes) {
            $lockedInvoice = Invoice::lockForUpdate()->findOrFail($invoice->id);

            $balance = $lockedInvoice->balance_due;
            if (Money::gt($amountStr, $balance)) {
                throw new InvalidArgumentException("Payment amount ({$amountStr}) exceeds balance due ({$balance}).");
            }

            $count = Payment::count() + 1;
            $paymentNumber = 'REC-'.date('Y').'-'.str_pad((string) $count, 5, '0', STR_PAD_LEFT);

            $payment = Payment::create([
                'payment_number' => $paymentNumber,
                'invoice_id' => $lockedInvoice->id,
                'amount' => $amountStr,
                'payment_method' => $paymentMethod,
                'paid_at' => now(),
                'status' => 'completed',
                'transaction_reference' => $reference,
                'collected_by' => $collector->id,
                'notes' => $notes,
            ]);

            $newPaidTotal = Money::add($lockedInvoice->paid_total, $amountStr);
            $newBalance = Money::sub($lockedInvoice->net_total, $newPaidTotal);

            $newStatus = Money::lte($newBalance, '0.00') ? 'paid' : 'partial';

            $lockedInvoice->update([
                'paid_total' => $newPaidTotal,
                'status' => $newStatus,
            ]);

            return $payment;
        });
    }
}
