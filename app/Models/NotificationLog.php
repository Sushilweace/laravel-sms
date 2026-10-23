<?php

namespace App\Models;

use App\Models\Traits\UserTrackable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    use HasFactory, UserTrackable;

    protected $fillable = [
        'recipient_id',
        'channel',
        'template_key',
        'title',
        'content',
        'status',
        'error_message',
        'sent_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}
