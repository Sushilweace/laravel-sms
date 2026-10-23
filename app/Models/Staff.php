<?php

namespace App\Models;

use App\Models\Traits\UserTrackable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Staff extends Model
{
    use HasFactory, LogsActivity, SoftDeletes, UserTrackable;

    protected $fillable = [
        'user_id',
        'employee_code',
        'first_name',
        'last_name',
        'gender',
        'qualification',
        'designation',
        'phone',
        'email',
        'joining_date',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'joining_date' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['employee_code', 'first_name', 'last_name', 'designation', 'status'])
            ->logOnlyDirty();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function teachingAssignments(): HasMany
    {
        return $this->hasMany(TeachingAssignment::class);
    }

    public function classTeacherSections(): HasMany
    {
        return $this->hasMany(Section::class, 'class_teacher_id');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function visibleSectionIds(): Collection
    {
        $assigned = TeachingAssignment::query()
            ->where('staff_id', $this->id)
            ->pluck('section_id');

        $classTeacher = Section::query()
            ->where('class_teacher_id', $this->id)
            ->pluck('id');

        return $assigned->merge($classTeacher)->unique()->values();
    }
}
