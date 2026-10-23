<?php

namespace App\Http\Controllers;

use App\Actions\Notices\PublishNotice;
use App\Models\Notice;
use App\Models\NoticeRead;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NoticeController extends Controller
{
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $notices = Notice::visibleTo($user)
            ->with(['creator', 'reads' => fn ($q) => $q->where('user_id', $user->id)])
            ->orderByDesc('is_pinned')
            ->latest('published_at')
            ->paginate(10);

        return view('notices.index', compact('notices'));
    }

    public function create(): View
    {
        if (! Auth::user()->hasAnyRole(['Super Admin', 'School Admin', 'Teacher'])) {
            abort(403, 'Unauthorized. Drafting notices requires staff privileges.');
        }

        $classes = SchoolClass::all();

        return view('notices.create', compact('classes'));
    }

    public function store(Request $request, PublishNotice $publisher): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->hasAnyRole(['Super Admin', 'School Admin', 'Teacher'])) {
            abort(403, 'Unauthorized. Publishing notices requires staff privileges.');
        }

        $data = $request->validate([
            'title' => 'required|string|max:150',
            'content' => 'required|string',
            'priority' => 'required|in:normal,important,emergency',
            'is_pinned' => 'boolean',
            'requires_ack' => 'boolean',
            'audience_type' => 'required|in:all,role,class',
            'role' => 'nullable|string',
            'class_id' => 'nullable|exists:school_classes,id',
        ]);

        $audiences = [];
        if ($data['audience_type'] === 'all') {
            $audiences[] = ['type' => 'all'];
        } elseif ($data['audience_type'] === 'role') {
            $audiences[] = ['type' => 'role', 'role' => $data['role']];
        } elseif ($data['audience_type'] === 'class') {
            $audiences[] = ['type' => 'class', 'target_id' => $data['class_id']];
        }

        $noticeData = [
            'title' => $data['title'],
            'content' => $data['content'],
            'priority' => $data['priority'],
            'is_pinned' => $request->boolean('is_pinned'),
            'requires_ack' => $request->boolean('requires_ack'),
        ];

        $publisher->execute($noticeData, $audiences, $user);

        return redirect()->route('notices.index')->with('success', 'Notice published successfully.');
    }

    public function acknowledge(Notice $notice): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        NoticeRead::updateOrCreate(
            [
                'notice_id' => $notice->id,
                'user_id' => $user->id,
            ],
            [
                'read_at' => now(),
                'acknowledged_at' => now(),
            ]
        );

        return back()->with('success', 'Notice acknowledged.');
    }
}
