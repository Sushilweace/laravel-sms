<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->longText('content');
            $table->string('status', 20)->default('published'); // draft, pending, approved, published, expired, cancelled
            $table->string('priority', 20)->default('normal'); // normal, important, emergency
            $table->boolean('is_pinned')->default(false);
            $table->boolean('requires_ack')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['published_at', 'expires_at']);
        });

        Schema::create('notice_audiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notice_id')->constrained('notices')->cascadeOnDelete();
            $table->string('type', 20)->default('all'); // all, role, class, section, user_list
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('role', 30)->nullable();
            $table->timestamps();
        });

        Schema::create('notice_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notice_id')->constrained('notices')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('read_at');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();

            $table->unique(['notice_id', 'user_id']);
        });

        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipient_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('channel', 25)->default('in_app'); // in_app, mail, sms, whatsapp
            $table->string('template_key', 80);
            $table->string('title', 150)->nullable();
            $table->text('content');
            $table->string('status', 20)->default('sent'); // queued, sent, delivered, failed, skipped
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('notice_reads');
        Schema::dropIfExists('notice_audiences');
        Schema::dropIfExists('notices');
    }
};
