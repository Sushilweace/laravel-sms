<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Concession extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'fee_head_id',
        'type',
        'amount_or_percentage',
        'reason',
        'approved_by',
        'is_active',
    ];

    protected $casts = [
        'amount_or_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function feeHead(): BelongsTo
    {
        return $this->belongsTo(FeeHead::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
