<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_calendar_days', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->string('type', 20)->default('holiday'); // holiday, working, half_day
            $table->string('title', 120);
            $table->string('applies_to', 20)->default('all');
            $table->timestamps();
        });

        Schema::create('attendance_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->date('date');
            $table->foreignId('marked_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('finalized_at')->nullable();
            $table->unsignedSmallInteger('present_count')->default(0);
            $table->unsignedSmallInteger('absent_count')->default(0);
            $table->unsignedSmallInteger('late_count')->default(0);
            $table->unsignedSmallInteger('leave_count')->default(0);
            $table->timestamps();

            $table->unique(['section_id', 'date']);
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnDelete();
            $table->date('date');
            $table->string('status', 15)->default('present'); // present, absent, late, leave
            $table->string('source', 20)->default('manual'); // manual, leave, import, device
            $table->string('edited_reason', 255)->nullable();
            $table->foreignId('last_edited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['enrollment_id', 'date']);
            $table->index(['date', 'enrollment_id']);
            $table->index(['date', 'status']);
        });

        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnDelete();
            $table->date('from_date');
            $table->date('to_date');
            $table->string('reason', 500);
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->string('status', 20)->default('pending'); // pending, approved, rejected, cancelled
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->string('decision_note', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('attendance_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnDelete();
            $table->date('month'); // YYYY-MM-01
            $table->unsignedTinyInteger('working_days')->default(0);
            $table->unsignedTinyInteger('present')->default(0);
            $table->unsignedTinyInteger('absent')->default(0);
            $table->unsignedTinyInteger('late')->default(0);
            $table->unsignedTinyInteger('leave')->default(0);
            $table->decimal('percentage', 5, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['enrollment_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_summaries');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('attendance_days');
        Schema::dropIfExists('school_calendar_days');
    }
};
