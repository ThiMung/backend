<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::query()
            ->where('status', 'published')
            ->with('organizer:id,name')
            ->withCount([
                'registrations as registered_count' => fn ($q) => $q->where('status', 'confirmed'),
            ])
            ->withAvg('reviews', 'rating');

        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhereHas('organizer', fn ($oq) => $oq->where('name', 'like', "%{$search}%"));
            });
        }

        $events = $query->orderBy('start_time')->get()->map(fn ($event) => $this->formatEvent($event));

        $categories = Event::where('status', '=', 'published', 'and')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->values();

        return response()->json([
            'events' => $events,
            'categories' => $categories,
        ]);
    }

    public function show(string $id)
    {
        $event = Event::query()
            ->where('status', 'published')
            ->with('organizer:id,name')
            ->withCount([
                'registrations as registered_count' => fn ($q) => $q->where('status', 'confirmed'),
            ])
            ->withAvg('reviews', 'rating')
            ->findOrFail($id);

        return response()->json($this->formatEvent($event));
    }

    private function formatEvent(Event $event): array
    {
        $registered = (int) ($event->registered_count ?? 0);
        $spotsLeft = max(0, $event->capacity - $registered);

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
            'capacity' => $event->capacity,
            'status' => $event->status,
            'organizer_name' => $event->organizer?->name,
            'registered_count' => $registered,
            'spots_left' => $spotsLeft,
            'is_full' => $spotsLeft === 0,
            'average_rating' => $event->reviews_avg_rating
                ? round((float) $event->reviews_avg_rating, 1)
                : null,
        ];
    }
}
