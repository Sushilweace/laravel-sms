<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_requests', function (Blueprint $table) {
            $table->id();
            $table->string('feature', 60); // report_remarks, notice_writer, question_generator, etc.
            $table->string('prompt_version', 20)->default('v1');
            $table->string('model', 60)->default('claude-3-5-sonnet');
            $table->unsignedInteger('tokens_used')->nullable();
            $table->decimal('cost', 8, 4)->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->longText('response_payload')->nullable();
            $table->string('status', 20)->default('completed'); // completed, failed
            $table->timestamps();
        });

        Schema::create('ai_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_request_id')->nullable()->constrained('ai_requests')->cascadeOnDelete();
            $table->string('feature', 60);
            $table->string('target_type', 80); // Mark, Notice, ExamSubject, etc.
            $table->unsignedBigInteger('target_id');
            $table->longText('suggested_content');
            $table->string('status', 20)->default('pending'); // pending, approved, rejected, edited
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();

            $table->index(['target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_suggestions');
        Schema::dropIfExists('ai_requests');
    }
};
