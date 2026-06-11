<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registration extends Model
{
    protected $fillable = [
        'event_id',
        'user_id',
        'status',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeWaitlisted(Builder $query): Builder
    {
        return $query->where('status', 'waitlist');
    }

    public function scopeForEvent(Builder $query, int $eventId): Builder
    {
        return $query->where('event_id', $eventId);
    }
}
