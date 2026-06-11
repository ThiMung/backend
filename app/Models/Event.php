<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = [
        'organizer_id',
        'title',
        'description',
        'location',
        'image_url',
        'category',
        'start_time',
        'end_time',
        'capacity',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'capacity' => 'integer',
    ];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->orderBy('created_at', 'desc');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeWithRegistrationCount(Builder $query): Builder
    {
        return $query->withCount([
            'registrations as registered_count' => fn ($q) => $q->where('status', 'confirmed'),
        ]);
    }
}
