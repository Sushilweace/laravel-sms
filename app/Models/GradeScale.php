<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradeScale extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_default',
        'description',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class)->orderByDesc('min_percentage');
    }
}
