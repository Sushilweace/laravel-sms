<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'feature',
        'prompt_version',
        'model',
        'tokens_used',
        'cost',
        'requested_by',
        'response_payload',
        'status',
    ];

    protected $casts = [
        'tokens_used' => 'integer',
        'cost' => 'decimal:4',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function suggestions(): HasMany
    {
        return $this->hasMany(AiSuggestion::class);
    }
}
