<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'month',
        'working_days',
        'present',
        'absent',
        'late',
        'leave',
        'percentage',
    ];

    protected $casts = [
        'month' => 'date',
        'working_days' => 'integer',
        'present' => 'integer',
        'absent' => 'integer',
        'late' => 'integer',
        'leave' => 'integer',
        'percentage' => 'decimal:2',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }
}
