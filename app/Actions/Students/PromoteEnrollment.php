<?php

namespace App\Actions\Students;

use App\Models\Enrollment;
use Illuminate\Support\Facades\DB;

class PromoteEnrollment
{
    public function execute(Enrollment $from, int $toYearId, int $toSectionId, string $outcome = 'promoted'): ?Enrollment
    {
        return DB::transaction(function () use ($from, $toYearId, $toSectionId, $outcome) {
            $from->update(['status' => $outcome]);

            if ($outcome === 'left') {
                $from->student->update(['status' => 'alumni']);

                return null;
            }

            return Enrollment::firstOrCreate(
                [
                    'student_id' => $from->student_id,
                    'academic_year_id' => $toYearId,
                ],
                [
                    'section_id' => $toSectionId,
                    'status' => 'active',
                    'enrolled_on' => now()->toDateString(),
                ]
            );
        });
    }
}
