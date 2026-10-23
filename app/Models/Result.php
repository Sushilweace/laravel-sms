<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Result extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'enrollment_id',
        'total_marks',
        'max_marks',
        'percentage',
        'grade',
        'gpa',
        'result_status',
        'section_rank',
        'class_rank',
        'failed_subjects',
    ];

    protected $casts = [
        'total_marks' => 'decimal:2',
        'max_marks' => 'decimal:2',
        'percentage' => 'decimal:2',
        'gpa' => 'decimal:2',
        'section_rank' => 'integer',
        'class_rank' => 'integer',
        'failed_subjects' => 'integer',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole(['Super Admin', 'School Admin'])) {
            return $query;
        }

        // Parents and students only see results if exam is published
        return $query->whereHas('exam', function ($q) {
            $q->where('status', 'published');
        })->whereHas('enrollment', function ($q) use ($user) {
            $q->visibleTo($user);
        });
    }
}
