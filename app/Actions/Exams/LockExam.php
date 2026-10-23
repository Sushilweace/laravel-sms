<?php

namespace App\Actions\Exams;

use App\Models\Exam;
use App\Models\User;
use InvalidArgumentException;

class LockExam
{
    public function execute(Exam $exam, User $user): void
    {
        if ($exam->status !== 'marks_open') {
            throw new InvalidArgumentException('Only exams with open marks entry can be locked.');
        }

        $exam->update([
            'status' => 'locked',
            'locked_at' => now(),
            'locked_by' => $user->id,
        ]);
    }
}
