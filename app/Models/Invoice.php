<?php

namespace App\Models;

use App\Models\Traits\UserTrackable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Invoice extends Model
{
    use HasFactory, LogsActivity, SoftDeletes, UserTrackable;

    protected $fillable = [
        'invoice_number',
        'enrollment_id',
        'academic_year_id',
        'period_key',
        'due_on',
        'total_amount',
        'discount_amount',
        'fine_amount',
        'paid_total',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'due_on' => 'date',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'fine_amount' => 'decimal:2',
        'paid_total' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['invoice_number', 'total_amount', 'paid_total', 'status'])
            ->logOnlyDirty();
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getNetTotalAttribute(): string
    {
        // total_amount - discount_amount + fine_amount
        $sub = bcsub((string) $this->total_amount, (string) $this->discount_amount, 2);

        return bcadd($sub, (string) $this->fine_amount, 2);
    }

    public function getBalanceDueAttribute(): string
    {
        $net = $this->net_total;
        $bal = bcsub($net, (string) $this->paid_total, 2);

        return bccomp($bal, '0.00', 2) > 0 ? $bal : '0.00';
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->where('due_on', '<', now()->toDateString());
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
