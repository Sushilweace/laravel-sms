<?php

namespace App\Models;

use App\Models\Traits\UserTrackable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    use HasFactory, UserTrackable;

    protected $fillable = [
        'name',
        'starts_on',
        'ends_on',
        'is_current',
        'is_locked',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'starts_on' => 'date',
        'ends_on' => 'date',
        'is_current' => 'boolean',
        'is_locked' => 'boolean',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    public function feeStructures(): HasMany
    {
        return $this->hasMany(FeeStructure::class);
    }

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_current', true);
    }

    public static function current(): ?static
    {
        $id = cache()->get('academic_year.current_id');
        if ($id) {
            $found = static::find($id);
            if ($found) {
                return $found;
            }
        }

        $year = static::where('is_current', 1)->first() ?? static::where('is_locked', 0)->first() ?? static::first();
        if ($year) {
            cache()->forever('academic_year.current_id', $year->id);
        }

        return $year;
    }
}
