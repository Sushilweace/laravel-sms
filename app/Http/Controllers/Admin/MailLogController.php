<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use App\Models\User;
use App\Services\Notifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MailLogController extends Controller
{
    public function index(Request $request): View
    {
        $channel = $request->input('channel', 'all');
        $status = $request->input('status', 'all');
        $search = trim($request->input('q', ''));

        $query = NotificationLog::with('recipient')->latest();

        if ($channel !== 'all') {
            $query->where('channel', $channel);
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")
                    ->orWhereHas('recipient', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        $logs = $query->paginate(25)->withQueryString();

        // Calculate statistics
        $totalCount = NotificationLog::count();
        $mailCount = NotificationLog::where('channel', 'mail')->count();
        $smsCount = NotificationLog::whereIn('channel', ['sms', 'whatsapp'])->count();
        $deliveredCount = NotificationLog::where('status', 'delivered')->count();
        $failedCount = NotificationLog::where('status', 'failed')->count();
        $deliveryRate = $totalCount > 0 ? round(($deliveredCount / $totalCount) * 100, 1) : 100;

        $stats = [
            'total' => $totalCount,
            'mail' => $mailCount,
            'sms' => $smsCount,
            'delivered' => $deliveredCount,
            'failed' => $failedCount,
            'delivery_rate' => $deliveryRate,
        ];

        return view('admin.logs.mail', compact('logs', 'stats', 'channel', 'status', 'search'));
    }

    public function show(NotificationLog $log): JsonResponse
    {
        $log->load('recipient');

        return response()->json([
            'id' => $log->id,
            'channel' => strtoupper($log->channel),
            'recipient_name' => $log->recipient?->name ?? 'External User',
            'recipient_email' => $log->recipient?->email,
            'recipient_phone' => $log->recipient?->phone,
            'template_key' => $log->template_key,
            'title' => $log->title,
            'content' => $log->content,
            'status' => $log->status,
            'error_message' => $log->error_message,
            'sent_at' => $log->sent_at?->format('d M Y, h:i A'),
            'created_at' => $log->created_at->format('d M Y, h:i A'),
        ]);
    }

    public function resend(NotificationLog $log): RedirectResponse
    {
        try {
            $user = $log->recipient;
            if (! $user) {
                return back()->with('error', 'Recipient user no longer exists.');
            }

            // Retry delivery
            $log->update(['status' => 'queued', 'error_message' => null]);
            Notifier::send([$user], $log->template_key, $log->title ?? 'Notification', $log->content, [$log->channel]);

            return back()->with('success', "Notification message #{$log->id} has been re-dispatched to {$user->name}.");
        } catch (\Throwable $e) {
            $log->update(['status' => 'failed', 'error_message' => $e->getMessage()]);

            return back()->with('error', "Re-dispatch failed: {$e->getMessage()}");
        }
    }

    public function sendTest(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'channel' => 'required|in:mail,sms,whatsapp,in_app',
            'title' => 'required|string|max:150',
            'content' => 'required|string',
        ]);

        $recipient = User::findOrFail($data['recipient_id']);

        Notifier::send(
            [$recipient],
            'test_notification',
            $data['title'],
            $data['content'],
            [$data['channel']]
        );

        return back()->with('success', "Test {$data['channel']} notification dispatched successfully to {$recipient->name}.");
    }
}
