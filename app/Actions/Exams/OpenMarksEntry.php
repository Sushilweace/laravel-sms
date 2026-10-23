<?php

namespace App\Actions\Exams;

use App\Models\Exam;
use InvalidArgumentException;

class OpenMarksEntry
{
    public function execute(Exam $exam): void
    {
        if ($exam->status !== 'draft') {
            throw new InvalidArgumentException('Only draft exams can be opened for marks entry.');
        }

        if ($exam->examSubjects()->count() === 0) {
            throw new InvalidArgumentException('Cannot open marks entry without any exam subjects configured.');
        }

        $exam->update(['status' => 'marks_open']);
    }
}
