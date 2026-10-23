<?php

namespace App\Actions\Academic;

use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;

class SetCurrentAcademicYear
{
    public function execute(AcademicYear $year): void
    {
        DB::transaction(function () use ($year) {
            AcademicYear::query()->where('is_current', true)->update(['is_current' => false]);
            $year->update(['is_current' => true]);
        });

        cache()->forget('academic_year.current');
    }
}
