<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'date',
        'location',
        'event_type',
        'is_recurring',
        'recurrence_pattern',
        'reminder',
    ];

    protected $casts = [
        'date' => 'datetime',
        'is_recurring' => 'boolean',
        'reminder' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now());
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('event_type', $type);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('date', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('date', now()->month)
            ->whereYear('date', now()->year);
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->date->format('M j, Y g:i A');
    }

    public function getFormattedTimeAttribute(): string
    {
        return $this->date->format('g:i A');
    }

    public function getEventTypeColorAttribute(): string
    {
        return match ($this->event_type) {
            'personal' => 'blue',
            'work' => 'green',
            'social' => 'purple',
            'family' => 'orange',
            default => 'gray',
        };
    }
}
