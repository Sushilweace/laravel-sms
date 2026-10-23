<?php

namespace App\Http\Controllers;

use App\Actions\Ai\GenerateReportRemarks;
use App\Models\AiSuggestion;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\User;
use App\Services\Ai\AiGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AiController extends Controller
{
    public function index(): View
    {
        $suggestions = AiSuggestion::with('aiRequest')->latest()->paginate(10);
        $exams = Exam::latest()->get();

        return view('ai.index', compact('suggestions', 'exams'));
    }

    public function generateRemarks(Request $request, GenerateReportRemarks $action): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $data = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'enrollment_id' => 'required|exists:enrollments,id',
        ]);

        $exam = Exam::findOrFail($data['exam_id']);
        $enrollment = Enrollment::findOrFail($data['enrollment_id']);

        $remark = $action->execute($exam, $enrollment, $user);

        return response()->json([
            'success' => true,
            'remark' => $remark,
        ]);
    }

    public function draftNotice(Request $request, AiGateway $gateway): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $data = $request->validate([
            'topic' => 'required|string|max:200',
            'tone' => 'nullable|string|in:formal,urgent,friendly',
        ]);

        $prompt = "Draft a school announcement regarding: {$data['topic']}. Tone: ".($data['tone'] ?? 'formal');

        $suggestion = $gateway->ask(
            'notice_writer',
            $prompt,
            'Notice',
            0,
            ['topic' => $data['topic']],
            $user
        );

        return response()->json([
            'success' => true,
            'draft' => $suggestion->suggested_content,
        ]);
    }

    public function reviewSuggestion(AiSuggestion $suggestion, Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $status = $request->input('status') === 'approved' ? 'approved' : 'rejected';
        $suggestion->update([
            'status' => $status,
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'review_notes' => $request->input('review_notes'),
        ]);

        return back()->with('success', "AI suggestion marked as {$status}.");
    }
}
