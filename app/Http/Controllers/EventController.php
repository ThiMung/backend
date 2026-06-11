<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\EventFormatterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct(
        private EventFormatterService $formatter
    ) {}

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
            ->get();

        $categories = Event::query()
            ->where('status', 'published')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->values();

        return response()->json([
            'events' => $this->formatter->formatCollection($events),
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

        return response()->json($this->formatter->formatEvent($event));
    }
}
