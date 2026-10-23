<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoticeAudience extends Model
{
    use HasFactory;

    protected $fillable = [
        'notice_id',
        'type',
        'target_id',
        'role',
    ];

    public function notice(): BelongsTo
    {
        return $this->belongsTo(Notice::class);
    }
}
