<?php

namespace App\Http\Controllers;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));

        $query = Event::query()
            ->where('status', 'published')
            ->where('status', '!=', 'ended')
            ->with('organizer:id,name')
            ->withCount([
                'registrations as registered_count' => fn ($query) => $query->where('status', 'confirmed'),
            ])
            ->withAvg('reviews', 'rating');

        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhereHas('organizer', fn ($organizerQuery) => $organizerQuery->where('name', 'like', "%{$search}%"));
            });
        }

        $events = $query
            ->orderBy('start_time')
            ->get()
            ->map(fn (Event $event) => $this->formatEvent($event));

        $categories = Event::query()
            ->where('status', 'published')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->values();

        return response()->json([
            'events' => $events,
            'categories' => $categories,
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $event = Event::query()
            ->whereIn('status', ['published', 'ended'])
            ->with('organizer:id,name')
            ->with(['reviews.user:id,name'])
            ->withCount([
                'registrations as registered_count' => fn ($query) => $query->where('status', 'confirmed'),
            ])
            ->withAvg('reviews', 'rating')
            ->findOrFail($id);

        return response()->json($this->formatEvent($event));
    }

    private function formatEvent(Event $event): array
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
}
