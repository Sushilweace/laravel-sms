<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('code', 20)->unique();
            $table->text('address')->nullable();
            $table->string('phone', 25)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('logo_path')->nullable();
            $table->string('timezone', 50)->default('Asia/Kolkata');
            $table->string('currency', 3)->default('INR');
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->json('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('schools');
    }
};
