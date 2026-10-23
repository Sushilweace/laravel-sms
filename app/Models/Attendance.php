<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Attendance extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'enrollment_id',
        'date',
        'status',
        'source',
        'edited_reason',
        'last_edited_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['enrollment_id', 'date', 'status', 'edited_reason'])
            ->logOnlyDirty();
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function lastEditedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_edited_by');
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole(['Super Admin', 'School Admin', 'Accountant'])) {
            return $query;
        }

        return $query->whereHas('enrollment', function ($q) use ($user) {
            $q->visibleTo($user);
        });
    }
}
