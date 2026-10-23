<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarkChange extends Model
{
    use HasFactory;

    protected $fillable = [
        'mark_id',
        'old_marks',
        'new_marks',
        'reason',
        'changed_by',
    ];

    protected $casts = [
        'old_marks' => 'decimal:2',
        'new_marks' => 'decimal:2',
    ];

    public function mark(): BelongsTo
    {
        return $this->belongsTo(Mark::class);
    }

    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
