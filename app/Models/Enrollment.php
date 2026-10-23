<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Enrollment extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'section_id',
        'roll_number',
        'status',
        'enrolled_on',
        'left_on',
    ];

    protected $casts = [
        'roll_number' => 'integer',
        'enrolled_on' => 'date',
        'left_on' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['roll_number', 'status', 'section_id'])
            ->logOnlyDirty();
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function marks(): HasMany
    {
        return $this->hasMany(Mark::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function concessions(): HasMany
    {
        return $this->hasMany(Concession::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function attendanceSummaries(): HasMany
    {
        return $this->hasMany(AttendanceSummary::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeForCurrentYear(Builder $query): Builder
    {
        return $query->whereHas('academicYear', fn ($q) => $q->where('is_current', true));
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole(['Super Admin', 'School Admin', 'Accountant'])) {
            return $query;
        }

        if ($user->hasRole('Teacher') && $user->staff) {
            $sectionIds = $user->staff->visibleSectionIds();

            return $query->whereIn('section_id', $sectionIds);
        }

        if ($user->hasRole('Parent') || $user->guardian) {
            $guardianId = $user->guardian?->id;

            return $query->whereHas('student.guardians', function ($q) use ($guardianId) {
                $q->where('guardians.id', $guardianId);
            });
        }

        if ($user->hasRole('Student') || $user->student) {
            return $query->whereHas('student', fn ($q) => $q->where('user_id', $user->id));
        }

        return $query->whereRaw('1 = 0');
    }
}
