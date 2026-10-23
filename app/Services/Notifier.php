<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class Notifier
{
    public static function send(User|array $recipients, string $templateKey, string $title, string $content, array $channels = ['in_app']): void
    {
        $users = is_array($recipients) ? $recipients : [$recipients];

        foreach ($users as $user) {
            foreach ($channels as $channel) {
                try {
                    $log = NotificationLog::create([
                        'recipient_id' => $user->id,
                        'channel' => $channel,
                        'template_key' => $templateKey,
                        'title' => $title,
                        'content' => $content,
                        'status' => 'queued',
                    ]);

                    self::deliver($user, $channel, $title, $content);

                    $log->update([
                        'status' => 'delivered',
                        'sent_at' => now(),
                    ]);
                } catch (\Throwable $e) {
                    Log::error("Notification failed: {$e->getMessage()}", [
                        'recipient_id' => $user->id,
                        'channel' => $channel,
                    ]);

                    if (isset($log)) {
                        $log->update([
                            'status' => 'failed',
                            'error_message' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }
    }

    protected static function deliver(User $user, string $channel, string $title, string $content): void
    {
        switch ($channel) {
            case 'in_app':
                // Stored in notification_logs table, queryable by user
                break;

            case 'mail':
                if (! empty($user->email)) {
                    // Send email or log locally if no mail server configured
                    Log::info("Sending email to {$user->email}: {$title}");
                }
                break;

            case 'sms':
                // Fake / driver-backed SMS logging
                Log::info("Sending SMS to {$user->phone}: {$content}");
                break;

            case 'whatsapp':
                // Fake / driver-backed WhatsApp logging
                Log::info("Sending WhatsApp to {$user->phone}: {$content}");
                break;
        }
    }
}
