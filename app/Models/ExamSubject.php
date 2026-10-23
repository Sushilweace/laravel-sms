<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamSubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'school_class_id',
        'subject_id',
        'max_marks',
        'pass_marks',
        'exam_date',
    ];

    protected $casts = [
        'max_marks' => 'decimal:2',
        'pass_marks' => 'decimal:2',
        'exam_date' => 'date',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function marks(): HasMany
    {
        return $this->hasMany(Mark::class);
    }
}
