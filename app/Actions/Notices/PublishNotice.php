<?php

namespace App\Actions\Notices;

use App\Models\Notice;
use App\Models\NoticeAudience;
use App\Models\User;
use App\Services\Notifier;
use Illuminate\Support\Facades\DB;

class PublishNotice
{
    public function execute(array $noticeData, array $audiences, User $author): Notice
    {
        return DB::transaction(function () use ($noticeData, $audiences, $author) {
            $noticeData['created_by'] = $author->id;
            $noticeData['status'] = $noticeData['status'] ?? 'published';
            $noticeData['published_at'] = $noticeData['published_at'] ?? now();

            $notice = Notice::create($noticeData);

            $targetUserIds = collect();

            foreach ($audiences as $aud) {
                NoticeAudience::create([
                    'notice_id' => $notice->id,
                    'type' => $aud['type'], // all, role, class, section, user_list
                    'target_id' => $aud['target_id'] ?? null,
                    'role' => $aud['role'] ?? null,
                ]);

                // Resolve recipients for notification
                if ($aud['type'] === 'all') {
                    $targetUserIds = $targetUserIds->merge(User::where('is_active', true)->pluck('id'));
                } elseif ($aud['type'] === 'role' && ! empty($aud['role'])) {
                    $targetUserIds = $targetUserIds->merge(User::role($aud['role'])->where('is_active', true)->pluck('id'));
                } elseif ($aud['type'] === 'user_list' && ! empty($aud['target_id'])) {
                    $targetUserIds->push($aud['target_id']);
                }
            }

            $users = User::whereIn('id', $targetUserIds->unique())->get();
            Notifier::send(
                $users->all(),
                'notice.published',
                $notice->title,
                substr(strip_tags($notice->content), 0, 160),
                ['in_app']
            );

            return $notice;
        });
    }
}
