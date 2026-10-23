<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'numeric_level',
        'next_class_id',
    ];

    protected $casts = [
        'numeric_level' => 'integer',
    ];

    public function nextClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'next_class_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'class_subjects')
            ->withPivot('is_optional')
            ->withTimestamps();
    }

    public function feeStructures(): HasMany
    {
        return $this->hasMany(FeeStructure::class);
    }
}
