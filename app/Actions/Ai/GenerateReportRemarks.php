<?php

namespace App\Actions\Ai;

use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\Result;
use App\Models\User;
use App\Services\Ai\AiGateway;

class GenerateReportRemarks
{
    public function __construct(protected AiGateway $aiGateway) {}

    public function execute(Exam $exam, Enrollment $enrollment, ?User $user = null): string
    {
        $result = Result::where('exam_id', $exam->id)
            ->where('enrollment_id', $enrollment->id)
            ->first();

        $pct = $result ? (float) $result->percentage : 75.0;

        $prompt = "Draft a constructive, motivating teacher report remark for student with overall percentage {$pct}% in exam {$exam->name}.";

        $suggestion = $this->aiGateway->ask(
            'report_remarks',
            $prompt,
            'Enrollment',
            $enrollment->id,
            ['percentage' => $pct],
            $user
        );

        return $suggestion->suggested_content;
    }
}
