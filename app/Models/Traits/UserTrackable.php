<?php

namespace App\Models\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

trait UserTrackable
{
    /**
     * In-memory cache of column existence per table to eliminate redundant information_schema queries.
     */
    protected static array $trackingColumnCache = [];

    protected static function hasTrackingColumn(string $table, string $column): bool
    {
        return static::$trackingColumnCache[$table][$column] ??= Schema::hasColumn($table, $column);
    }

    public static function bootUserTrackable(): void
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                $table = $model->getTable();
                if (empty($model->created_by) && static::hasTrackingColumn($table, 'created_by')) {
                    $model->created_by = Auth::id();
                }
                if (empty($model->updated_by) && static::hasTrackingColumn($table, 'updated_by')) {
                    $model->updated_by = Auth::id();
                }
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $table = $model->getTable();
                if (static::hasTrackingColumn($table, 'updated_by')) {
                    $model->updated_by = Auth::id();
                }
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
