<?php

namespace App\Models;

use App\Models\Traits\UserTrackable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notice extends Model
{
    use HasFactory, UserTrackable;

    protected $fillable = [
        'title',
        'content',
        'status',
        'priority',
        'is_pinned',
        'requires_ack',
        'published_at',
        'expires_at',
        'created_by',
        'approved_by',
        'updated_by',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'requires_ack' => 'boolean',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function audiences(): HasMany
    {
        return $this->hasMany(NoticeAudience::class);
    }

    public function reads(): HasMany
    {
        return $this->hasMany(NoticeRead::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            });
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole(['Super Admin', 'School Admin'])) {
            return $query;
        }

        // Check if there is an audience for "all", or matching user's role, or user id
        return $query->published()->where(function ($q) use ($user) {
            $q->whereHas('audiences', function ($sub) use ($user) {
                $sub->where('type', 'all')
                    ->orWhere(function ($r) use ($user) {
                        $r->where('type', 'role')->whereIn('role', $user->roles->pluck('name'));
                    })
                    ->orWhere(function ($u) use ($user) {
                        $u->where('type', 'user_list')->where('target_id', $user->id);
                    });
            });
        });
    }
}
