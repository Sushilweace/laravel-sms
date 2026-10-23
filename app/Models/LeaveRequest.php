<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'from_date',
        'to_date',
        'reason',
        'requested_by',
        'status',
        'decided_by',
        'decided_at',
        'decision_note',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'decided_at' => 'datetime',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole(['Super Admin', 'School Admin'])) {
            return $query;
        }

        return $query->whereHas('enrollment', function ($q) use ($user) {
            $q->visibleTo($user);
        });
    }
}
