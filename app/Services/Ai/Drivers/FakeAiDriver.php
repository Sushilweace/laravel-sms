<?php

namespace App\Services\Ai\Drivers;

use App\Services\Ai\Contracts\AiGatewayInterface;

class FakeAiDriver implements AiGatewayInterface
{
    public function generate(string $prompt, string $feature, array $options = []): string
    {
        switch ($feature) {
            case 'report_remarks':
                $pct = $options['percentage'] ?? 85;
                if ($pct >= 90) {
                    return 'Outstanding academic performance throughout the term. Demonstrates exceptional analytical skills and consistent dedication.';
                } elseif ($pct >= 75) {
                    return 'Good academic progress and active classroom participation. With focused revision, can achieve top grades.';
                } elseif ($pct >= 50) {
                    return 'Satisfactory effort. Needs regular practice and dedicated homework routine to strengthen core subject concepts.';
                } else {
                    return 'Needs immediate academic support and remedial sessions. Parents are requested to monitor daily study schedule closely.';
                }

            case 'notice_writer':
                return "Dear Parents & Students,\r\n\r\nThis is to notify you regarding the upcoming school event scheduled for next week. Please ensure timely arrival and appropriate uniform.\r\n\r\nRegards,\r\nPrincipal";

            case 'fee_reminder':
                $studentName = $options['student_name'] ?? 'Student';
                $amount = $options['amount'] ?? '5,000';

                return "Dear Parent, gentle reminder that fee dues of {$amount} for {$studentName} are payable by the due date. Thank you for your continued partnership.";

            default:
                return "Generated suggestion for feature: {$feature}";
        }
    }
}
