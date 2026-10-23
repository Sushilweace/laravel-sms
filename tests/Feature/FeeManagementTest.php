<?php

namespace Tests\Feature;

use App\Actions\Fees\CollectPayment;
use App\Actions\Fees\GenerateInvoices;
use App\Actions\Fees\ReversePayment;
use App\Models\Invoice;
use App\Models\User;
use App\Support\Money;
use Tests\TestCase;

class FeeManagementTest extends TestCase
{
    public function test_invoice_generation_is_idempotent(): void
    {
        $generator = new GenerateInvoices;

        // Run once
        $res1 = $generator->execute('2026-TEST-PERIOD');

        // Run second time with same period key
        $res2 = $generator->execute('2026-TEST-PERIOD');

        // The second run must skip all existing invoices
        $this->assertEquals(0, $res2['generated']);
        $this->assertGreaterThan(0, $res2['skipped']);
    }

    public function test_collecting_payment_reduces_balance(): void
    {
        $accountant = User::where('email', 'accountant@school.test')->first();
        $invoice = Invoice::where('status', 'open')->first();

        if (! $accountant || ! $invoice) {
            $this->markTestSkipped('Open invoice and accountant required.');
        }

        $collector = new CollectPayment;
        $payAmount = '500.00';
        $initialBalance = $invoice->balance_due;

        $payment = $collector->execute($invoice, $payAmount, 'cash', $accountant);

        $this->assertNotNull($payment);
        $this->assertEquals('completed', $payment->status);

        $invoice->refresh();
        $expectedBalance = Money::sub($initialBalance, $payAmount);
        $this->assertEquals($expectedBalance, $invoice->balance_due);

        // Test reversal
        $reverser = new ReversePayment;
        $reverser->execute($payment, 'Test reversal', $accountant);

        $invoice->refresh();
        $this->assertEquals($initialBalance, $invoice->balance_due);
    }
}
