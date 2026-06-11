<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Collection;

class EventFormatterService
{
    public function formatEvent(Event $event): array
    {
        $registered = (int) ($event->registered_count ?? 0);
        $capacity = (int) $event->capacity;
        $spotsLeft = max(0, $capacity - $registered);

        return [
            'id' => $event->id,
            'organizer_id' => $event->organizer_id,
            'title' => $event->title,
            'description' => $event->description,
            'location' => $event->location,
            'image_url' => $event->image_url,
            'category' => $event->category,
            'start_time' => $event->start_time,
            'end_time' => $event->end_time,
            'capacity' => $capacity,
            'status' => $event->status,
            'organizer_name' => $event->organizer?->name,
            'registered_count' => $registered,
            'spots_left' => $spotsLeft,
            'is_full' => $spotsLeft === 0,
            'average_rating' => $event->reviews_avg_rating === null
                ? null
                : round((float) $event->reviews_avg_rating, 1),
            'reviews' => $event->relationLoaded('reviews')
                ? $event->reviews->map(fn ($review) => [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'created_at' => $review->created_at?->toDateTimeString(),
                    'user' => $review->user ? ['id' => $review->user->id, 'name' => $review->user->name] : null,
                ])->toArray()
                : [],
        ];
    }

    public function formatCollection(Collection $events): Collection
    {
        return $events->map(fn (Event $event) => $this->formatEvent($event));
    }
}
