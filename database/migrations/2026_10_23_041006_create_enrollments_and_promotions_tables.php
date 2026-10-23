<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->foreignId('section_id')->constrained('sections')->restrictOnDelete();
            $table->unsignedSmallInteger('roll_number')->nullable();
            $table->string('status', 20)->default('active'); // active, promoted, retained, left
            $table->date('enrolled_on');
            $table->date('left_on')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'academic_year_id']);
            $table->index(['academic_year_id', 'section_id']);
        });

        Schema::create('promotion_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->foreignId('to_academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->foreignId('executed_by')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('promoted_count')->default(0);
            $table->unsignedInteger('retained_count')->default(0);
            $table->unsignedInteger('left_count')->default(0);
            $table->string('status', 20)->default('completed');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_runs');
        Schema::dropIfExists('enrollments');
    }
};
