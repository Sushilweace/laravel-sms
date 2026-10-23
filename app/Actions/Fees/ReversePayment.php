<?php

namespace App\Actions\Fees;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ReversePayment
{
    public function execute(Payment $payment, string $reason, User $user): Payment
    {
        if ($payment->status !== 'completed') {
            throw new InvalidArgumentException('Only completed payments can be reversed.');
        }

        if (empty($reason)) {
            throw new InvalidArgumentException('A reason is required to reverse a payment.');
        }

        return DB::transaction(function () use ($payment, $reason, $user) {
            $lockedInvoice = Invoice::lockForUpdate()->findOrFail($payment->invoice_id);

            $payment->update([
                'status' => 'reversed',
                'reversal_reason' => $reason,
                'reversed_by' => $user->id,
                'reversed_at' => now(),
            ]);

            $newPaidTotal = Money::sub($lockedInvoice->paid_total, (string) $payment->amount);
            if (Money::lt($newPaidTotal, '0.00')) {
                $newPaidTotal = '0.00';
            }

            $newBalance = Money::sub($lockedInvoice->net_total, $newPaidTotal);
            $newStatus = Money::lte($newPaidTotal, '0.00') ? 'open' : (Money::lte($newBalance, '0.00') ? 'paid' : 'partial');

            $lockedInvoice->update([
                'paid_total' => $newPaidTotal,
                'status' => $newStatus,
            ]);

            return $payment;
        });
    }
}
