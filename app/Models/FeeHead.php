<?php

namespace App\Models;

use App\Models\Traits\UserTrackable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeHead extends Model
{
    use HasFactory, UserTrackable;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_refundable',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_refundable' => 'boolean',
    ];

    public function structures(): HasMany
    {
        return $this->hasMany(FeeStructure::class);
    }
}
