<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id',
        'date',
        'marked_by',
        'finalized_at',
        'present_count',
        'absent_count',
        'late_count',
        'leave_count',
    ];

    protected $casts = [
        'date' => 'date',
        'finalized_at' => 'datetime',
        'present_count' => 'integer',
        'absent_count' => 'integer',
        'late_count' => 'integer',
        'leave_count' => 'integer',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function markedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
