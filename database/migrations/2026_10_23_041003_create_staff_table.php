<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('employee_code', 30)->unique();
            $table->string('first_name', 80);
            $table->string('last_name', 80);
            $table->string('gender', 15)->default('other');
            $table->string('qualification', 120)->nullable();
            $table->string('designation', 80)->default('Teacher');
            $table->string('phone', 25)->nullable();
            $table->string('email', 100)->nullable();
            $table->date('joining_date')->nullable();
            $table->string('status', 20)->default('active'); // active, inactive, resigned
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
