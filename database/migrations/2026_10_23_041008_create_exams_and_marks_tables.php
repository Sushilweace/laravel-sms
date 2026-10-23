<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_scales', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->boolean('is_default')->default(false);
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_scale_id')->constrained('grade_scales')->cascadeOnDelete();
            $table->string('name', 20); // A1, A2, B1, etc.
            $table->decimal('min_percentage', 5, 2);
            $table->decimal('max_percentage', 5, 2);
            $table->decimal('grade_point', 4, 2)->default(0.00);
            $table->string('description', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('term', 40)->nullable(); // Term 1, Term 2, Annual
            $table->foreignId('academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 20)->default('draft'); // draft, marks_open, locked, published
            $table->decimal('weightage', 5, 2)->nullable();
            $table->decimal('pass_percentage', 5, 2)->default(33.00);
            $table->foreignId('grade_scale_id')->nullable()->constrained('grade_scales')->nullOnDelete();
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('results_computed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('exam_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->decimal('max_marks', 6, 2)->default(100.00);
            $table->decimal('pass_marks', 6, 2)->default(33.00);
            $table->date('exam_date')->nullable();
            $table->timestamps();

            $table->unique(['exam_id', 'school_class_id', 'subject_id']);
        });

        Schema::create('marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_subject_id')->constrained('exam_subjects')->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnDelete();
            $table->decimal('marks_obtained', 6, 2)->nullable();
            $table->boolean('is_absent')->default(false);
            $table->decimal('grace', 5, 2)->default(0.00);
            $table->string('grace_reason', 255)->nullable();
            $table->string('teacher_remarks', 255)->nullable();
            $table->foreignId('entered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['exam_subject_id', 'enrollment_id']);
            $table->index('enrollment_id');
        });

        Schema::create('mark_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mark_id')->constrained('marks')->cascadeOnDelete();
            $table->decimal('old_marks', 6, 2)->nullable();
            $table->decimal('new_marks', 6, 2)->nullable();
            $table->string('reason', 255);
            $table->foreignId('changed_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnDelete();
            $table->decimal('total_marks', 8, 2)->default(0.00);
            $table->decimal('max_marks', 8, 2)->default(0.00);
            $table->decimal('percentage', 5, 2)->default(0.00);
            $table->string('grade', 10)->nullable();
            $table->decimal('gpa', 4, 2)->nullable();
            $table->string('result_status', 20)->default('pass'); // pass, fail, withheld
            $table->unsignedSmallInteger('section_rank')->nullable();
            $table->unsignedSmallInteger('class_rank')->nullable();
            $table->unsignedTinyInteger('failed_subjects')->default(0);
            $table->timestamps();

            $table->unique(['exam_id', 'enrollment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('results');
        Schema::dropIfExists('mark_changes');
        Schema::dropIfExists('marks');
        Schema::dropIfExists('exam_subjects');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('grades');
        Schema::dropIfExists('grade_scales');
    }
};
