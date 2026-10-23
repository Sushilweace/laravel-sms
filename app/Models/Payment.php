<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Payment extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'payment_number',
        'invoice_id',
        'amount',
        'payment_method',
        'paid_at',
        'status',
        'transaction_reference',
        'reversal_reason',
        'reversed_by',
        'reversed_at',
        'collected_by',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'reversed_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['payment_number', 'amount', 'status', 'reversal_reason'])
            ->logOnlyDirty();
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function reverser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isReversed(): bool
    {
        return $this->status === 'reversed';
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole(['Super Admin', 'School Admin', 'Accountant'])) {
            return $query;
        }

        return $query->whereHas('invoice.enrollment', function ($q) use ($user) {
            $q->visibleTo($user);
        });
    }
}
