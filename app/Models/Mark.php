<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Mark extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'exam_subject_id',
        'enrollment_id',
        'marks_obtained',
        'is_absent',
        'grace',
        'grace_reason',
        'teacher_remarks',
        'entered_by',
        'updated_by',
    ];

    protected $casts = [
        'marks_obtained' => 'decimal:2',
        'is_absent' => 'boolean',
        'grace' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['marks_obtained', 'is_absent', 'grace', 'teacher_remarks'])
            ->logOnlyDirty();
    }

    public function examSubject(): BelongsTo
    {
        return $this->belongsTo(ExamSubject::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    public function changes(): HasMany
    {
        return $this->hasMany(MarkChange::class);
    }

    public function getEffectiveMarksAttribute(): ?string
    {
        if ($this->is_absent) {
            return null;
        }

        $marks = $this->marks_obtained ?? '0.00';
        $grace = $this->grace ?? '0.00';

        return bcadd($marks, $grace, 2);
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole(['Super Admin', 'School Admin'])) {
            return $query;
        }

        return $query->whereHas('enrollment', function ($q) use ($user) {
            $q->visibleTo($user);
        });
    }
}
