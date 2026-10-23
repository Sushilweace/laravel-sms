<?php

namespace App\Actions\Students;

use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AdmitStudent
{
    public function execute(array $studentData, array $guardianData, int $sectionId, ?int $academicYearId = null): Student
    {
        $yearId = $academicYearId ?? AcademicYear::current()?->id;
        if (! $yearId) {
            throw new InvalidArgumentException('No active academic year found for admission.');
        }

        $year = AcademicYear::findOrFail($yearId);
        if ($year->is_locked) {
            throw new InvalidArgumentException('Cannot admit student into a locked academic year.');
        }

        return DB::transaction(function () use ($studentData, $guardianData, $sectionId, $yearId) {
            // Generate Admission Number if not provided
            if (empty($studentData['admission_no'])) {
                $count = Student::count() + 1;
                $studentData['admission_no'] = 'ADM-'.date('Y').'-'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
            }

            // Create Student
            $student = Student::create($studentData);

            // Re-use or create guardian by phone
            $guardian = null;
            if (! empty($guardianData['phone'])) {
                $guardian = Guardian::firstOrCreate(
                    ['phone' => $guardianData['phone']],
                    $guardianData
                );
            } elseif (! empty($guardianData['first_name'])) {
                $guardian = Guardian::create($guardianData);
            }

            if ($guardian) {
                $student->guardians()->attach($guardian->id, ['is_primary' => true]);
            }

            // Create initial enrollment
            Enrollment::create([
                'student_id' => $student->id,
                'academic_year_id' => $yearId,
                'section_id' => $sectionId,
                'roll_number' => $studentData['roll_number'] ?? null,
                'status' => 'active',
                'enrolled_on' => $studentData['admission_date'] ?? now()->toDateString(),
            ]);

            return $student;
        });
    }
}
