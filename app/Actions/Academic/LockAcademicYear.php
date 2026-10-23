<?php

namespace App\Actions\Academic;

use App\Models\AcademicYear;

class LockAcademicYear
{
    public function execute(AcademicYear $year): void
    {
        $year->update(['is_locked' => true]);
        cache()->forget('academic_year.current');
    }
}
