<?php

namespace App\Actions\Fees;

use App\Models\AcademicYear;
use App\Models\Concession;
use App\Models\Enrollment;
use App\Models\FeeStructure;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class GenerateInvoices
{
    public function execute(string $periodKey, ?string $dueDate = null, ?int $academicYearId = null): array
    {
        $yearId = $academicYearId ?? AcademicYear::current()?->id;
        if (! $yearId) {
            throw new InvalidArgumentException('Active academic year not found.');
        }

        $year = AcademicYear::findOrFail($yearId);
        if ($year->is_locked) {
            throw new InvalidArgumentException('Cannot generate invoices for a locked academic year.');
        }

        $dueOn = $dueDate ? Carbon::parse($dueDate)->toDateString() : now()->addDays(15)->toDateString();

        $enrollments = Enrollment::where('academic_year_id', $yearId)
            ->where('status', 'active')
            ->with(['section.schoolClass'])
            ->get();

        $generatedCount = 0;
        $skippedCount = 0;

        foreach ($enrollments as $enrollment) {
            // Check if already generated for this period
            if (Invoice::where('enrollment_id', $enrollment->id)->where('period_key', $periodKey)->exists()) {
                $skippedCount++;

                continue;
            }

            $classId = $enrollment->section?->school_class_id;
            if (! $classId) {
                continue;
            }

            $structures = FeeStructure::where('academic_year_id', $yearId)
                ->where('school_class_id', $classId)
                ->with('feeHead')
                ->get();

            if ($structures->isEmpty()) {
                continue;
            }

            DB::transaction(function () use ($enrollment, $periodKey, $dueOn, $structures, $yearId, &$generatedCount) {
                $count = Invoice::count() + 1;
                $invoiceNumber = 'INV-'.date('Y').'-'.str_pad((string) $count, 5, '0', STR_PAD_LEFT);

                $concessions = Concession::where('enrollment_id', $enrollment->id)
                    ->where('is_active', true)
                    ->get();

                $invoice = Invoice::create([
                    'invoice_number' => $invoiceNumber,
                    'enrollment_id' => $enrollment->id,
                    'academic_year_id' => $yearId,
                    'period_key' => $periodKey,
                    'due_on' => $dueOn,
                    'total_amount' => '0.00',
                    'discount_amount' => '0.00',
                    'fine_amount' => '0.00',
                    'paid_total' => '0.00',
                    'status' => 'open',
                ]);

                $totalAmount = '0.00';
                $totalDiscount = '0.00';

                foreach ($structures as $fee) {
                    $itemAmount = (string) $fee->amount;
                    $itemDiscount = '0.00';

                    // Check concession for this fee head or general concession
                    $concession = $concessions->first(fn ($c) => $c->fee_head_id === $fee->fee_head_id || $c->fee_head_id === null);
                    if ($concession) {
                        if ($concession->type === 'percentage') {
                            $itemDiscount = Money::percent($itemAmount, (string) $concession->amount_or_percentage);
                        } else {
                            $itemDiscount = (string) min((float) $itemAmount, (float) $concession->amount_or_percentage);
                        }
                    }

                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'fee_head_id' => $fee->fee_head_id,
                        'amount' => $itemAmount,
                        'discount_amount' => $itemDiscount,
                    ]);

                    $totalAmount = Money::add($totalAmount, $itemAmount);
                    $totalDiscount = Money::add($totalDiscount, $itemDiscount);
                }

                $invoice->update([
                    'total_amount' => $totalAmount,
                    'discount_amount' => $totalDiscount,
                ]);

                $generatedCount++;
            });
        }

        return [
            'generated' => $generatedCount,
            'skipped' => $skippedCount,
        ];
    }
}
