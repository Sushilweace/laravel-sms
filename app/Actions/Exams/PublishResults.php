<?php

namespace App\Actions\Exams;

use App\Models\Exam;
use InvalidArgumentException;

class PublishResults
{
    public function execute(Exam $exam): void
    {
        if ($exam->status !== 'locked') {
            throw new InvalidArgumentException('Only locked exams with computed results can be published.');
        }

        if (! $exam->results_computed_at) {
            throw new InvalidArgumentException('Cannot publish exam: results have not been computed yet.');
        }

        $exam->update([
            'status' => 'published',
        ]);
    }
}
