<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Student extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'user_id',
        'admission_no',
        'first_name',
        'last_name',
        'gender',
        'date_of_birth',
        'blood_group',
        'religion',
        'caste',
        'address',
        'city',
        'state',
        'postal_code',
        'photo_path',
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['admission_no', 'first_name', 'last_name', 'status'])
            ->logOnlyDirty();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(Guardian::class, 'guardian_student')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(StudentDocument::class);
    }

    public function currentEnrollment(): HasOne
    {
        return $this->hasOne(Enrollment::class)
            ->whereHas('academicYear', fn ($q) => $q->where('is_current', true))
            ->latestOfMany();
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
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

            return $query->whereHas('enrollments', function ($q) use ($sectionIds) {
                $q->whereIn('section_id', $sectionIds);
            });
        }

        if ($user->hasRole('Parent') || $user->guardian) {
            $guardianId = $user->guardian?->id;

            return $query->whereHas('guardians', function ($q) use ($guardianId) {
                $q->where('guardians.id', $guardianId);
            });
        }

        if ($user->hasRole('Student') || $user->student) {
            return $query->where('user_id', $user->id);
        }

        return $query->whereRaw('1 = 0');
    }
}
