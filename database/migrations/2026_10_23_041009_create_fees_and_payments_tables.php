<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_heads', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('code', 30)->unique();
            $table->string('description', 255)->nullable();
            $table->boolean('is_refundable')->default(false);
            $table->timestamps();
        });

        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_head_id')->constrained('fee_heads')->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('frequency', 20)->default('monthly'); // once, monthly, quarterly, yearly
            $table->unsignedTinyInteger('due_day')->default(10);
            $table->timestamps();
        });

        Schema::create('concessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnDelete();
            $table->foreignId('fee_head_id')->nullable()->constrained('fee_heads')->nullOnDelete();
            $table->string('type', 20)->default('percentage'); // percentage, fixed
            $table->decimal('amount_or_percentage', 12, 2);
            $table->string('reason', 255);
            $table->foreignId('approved_by')->constrained('users')->restrictOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique();
            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->string('period_key', 50); // e.g. 2026-04, 2026-TERM1
            $table->date('due_on');
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->decimal('fine_amount', 12, 2)->default(0.00);
            $table->decimal('paid_total', 12, 2)->default(0.00);
            $table->string('status', 20)->default('open'); // open, partial, paid, overdue, cancelled
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['enrollment_id', 'period_key']);
            $table->index(['status', 'due_on']);
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('fee_head_id')->constrained('fee_heads')->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number', 50)->unique();
            $table->foreignId('invoice_id')->constrained('invoices')->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 25)->default('cash'); // cash, upi, card, bank, cheque, online
            $table->timestamp('paid_at');
            $table->string('status', 20)->default('completed'); // completed, pending, bounced, reversed
            $table->string('transaction_reference', 100)->nullable();
            $table->string('reversal_reason', 255)->nullable();
            $table->foreignId('reversed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reversed_at')->nullable();
            $table->foreignId('collected_by')->constrained('users')->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('paid_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('concessions');
        Schema::dropIfExists('fee_structures');
        Schema::dropIfExists('fee_heads');
    }
};
