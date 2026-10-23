<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolCalendarDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'type',
        'title',
        'applies_to',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
