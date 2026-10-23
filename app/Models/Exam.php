<?php

namespace App\Models;

use App\Models\Traits\UserTrackable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Exam extends Model
{
    use HasFactory, LogsActivity, SoftDeletes, UserTrackable;

    protected $fillable = [
        'name',
        'term',
        'academic_year_id',
        'start_date',
        'end_date',
        'status',
        'weightage',
        'pass_percentage',
        'grade_scale_id',
        'locked_at',
        'locked_by',
        'results_computed_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'locked_at' => 'datetime',
        'results_computed_at' => 'datetime',
        'weightage' => 'decimal:2',
        'pass_percentage' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'status', 'locked_at', 'results_computed_at'])
            ->logOnlyDirty();
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function gradeScale(): BelongsTo
    {
        return $this->belongsTo(GradeScale::class);
    }

    public function lockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function examSubjects(): HasMany
    {
        return $this->hasMany(ExamSubject::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isMarksOpen(): bool
    {
        return $this->status === 'marks_open';
    }

    public function isLocked(): bool
    {
        return $this->status === 'locked';
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}
