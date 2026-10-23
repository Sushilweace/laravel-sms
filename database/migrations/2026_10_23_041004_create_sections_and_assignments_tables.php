<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->string('name', 20); // A, B, C
            $table->unsignedSmallInteger('capacity')->nullable()->default(40);
            $table->foreignId('class_teacher_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->timestamps();

            $table->unique(['school_class_id', 'academic_year_id', 'name']);
        });

        Schema::create('class_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->boolean('is_optional')->default(false);
            $table->timestamps();

            $table->unique(['school_class_id', 'subject_id']);
        });

        Schema::create('teaching_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['section_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_assignments');
        Schema::dropIfExists('class_subjects');
        Schema::dropIfExists('sections');
    }
};
