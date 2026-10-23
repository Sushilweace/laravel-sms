<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('admission_no', 30)->unique();
            $table->string('first_name', 80);
            $table->string('last_name', 80);
            $table->string('gender', 15)->default('other');
            $table->date('date_of_birth');
            $table->string('blood_group', 10)->nullable();
            $table->string('religion', 50)->nullable();
            $table->string('caste', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 60)->nullable();
            $table->string('state', 60)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('photo_path')->nullable();
            $table->string('status', 20)->default('active'); // active, alumni, transferred, inactive
            $table->timestamps();
            $table->softDeletes();

            $table->index(['last_name', 'first_name']);
            $table->index('status');
        });

        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('first_name', 80);
            $table->string('last_name', 80);
            $table->string('relation', 30)->default('Father'); // Father, Mother, Guardian
            $table->string('phone', 25)->index();
            $table->string('email', 100)->nullable();
            $table->string('occupation', 80)->nullable();
            $table->decimal('annual_income', 12, 2)->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        Schema::create('guardian_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guardian_id')->constrained('guardians')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->boolean('is_primary')->default(true);
            $table->timestamps();

            $table->unique(['guardian_id', 'student_id']);
        });

        Schema::create('student_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('document_type', 40); // birth_certificate, transfer_certificate, etc.
            $table->string('title', 150);
            $table->string('file_path');
            $table->unsignedInteger('file_size')->default(0);
            $table->string('mime_type', 100)->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_documents');
        Schema::dropIfExists('guardian_student');
        Schema::dropIfExists('guardians');
        Schema::dropIfExists('students');
    }
};
