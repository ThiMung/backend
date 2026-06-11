<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Requests\UpdateEventStatusRequest;
use App\Models\Event;
use App\Models\Registration;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class OrganizerEventController extends Controller
{
    private const DEFAULT_EVENT_IMAGE = 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?q=80&w=800';

    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function index(): JsonResponse
    {
        $events = Event::query()
            ->where('organizer_id', Auth::id())
            ->withCount([
                'registrations as registered_count' => fn ($query) => $query->where('status', 'confirmed'),
            ])
            ->orderBy('start_time')
            ->get();

        return response()->json(['events' => $events]);
    }

    public function store(StoreEventRequest $request): JsonResponse
    {
        $data = $request->validated();
        $startTime = Carbon::parse($data['start_time']);

        // Sự kiện mới luôn là draft để organizer kiểm tra trước khi công khai.
        $event = Event::create([
            'organizer_id' => Auth::id(),
            'title' => $data['title'],
            'description' => $data['description'],
            'location' => $data['location'],
            'category' => $data['category'],
            'start_time' => $startTime,
            'end_time' => $startTime->copy()->addHours(3),
            'capacity' => $data['capacity'],
            'image_url' => $data['image_url'] ?? self::DEFAULT_EVENT_IMAGE,
            'status' => 'draft',
        ]);

        $this->loadRegistrationCount($event);

        return response()->json([
            'message' => 'Event created successfully as draft!',
            'event' => $event,
        ], 201);
    }

    public function show(Event $event): JsonResponse
    {
        $this->authorizeOrganizer($event);
        $event->load([
            'organizer:id,name',
            'reviews.user:id,name',
        ]);
        $this->loadRegistrationCount($event);
        $event->loadAvg('reviews', 'rating');

        return response()->json(['event' => $event]);
    }

    public function update(UpdateEventRequest $request, Event $event): JsonResponse
    {
        $this->authorizeOrganizer($event);

        $data = $request->validated();
        $startTime = Carbon::parse($data['start_time']);

        $event->update([
            'title' => $data['title'],
            'description' => $data['description'],
            'location' => $data['location'],
            'category' => $data['category'],
            'start_time' => $startTime,
            'end_time' => $startTime->copy()->addHours(3),
            'capacity' => $data['capacity'],
            'image_url' => $data['image_url'] ?? $event->image_url ?? self::DEFAULT_EVENT_IMAGE,
        ]);

        $userIds = Registration::query()
            ->where('event_id', $event->id)
            ->whereIn('status', ['confirmed', 'waitlist'])
            ->pluck('user_id')
            ->toArray();

        $this->notificationService->notifyMultipleUsers(
            $userIds,
            $event,
            'event_updated',
            'Event updated',
            "The event {$event->title} has been updated."
        );

        $this->loadRegistrationCount($event);

        return response()->json([
            'message' => 'Event updated successfully.',
            'event' => $event,
        ]);
    }

    public function updateStatus(UpdateEventStatusRequest $request, Event $event): JsonResponse
    {
        $this->authorizeOrganizer($event);

        $data = $request->validated();
        $nextStatus = $data['status'];
    
        $event->update(['status' => $nextStatus]);

        if ($nextStatus === 'cancelled') {
            $userIds = Registration::query()
                ->where('event_id', $event->id)
                ->whereIn('status', ['confirmed', 'waitlist'])
                ->pluck('user_id')
                ->toArray();

            $this->notificationService->notifyMultipleUsers(
                $userIds,
                $event,
                'event_cancelled',
                'Event cancelled',
                "The event {$event->title} has been cancelled."
            );
        }

        $this->loadRegistrationCount($event);

        return response()->json([
            'message' => 'Event status updated successfully.',
            'event' => $event,
        ]);
    }

    public function destroy(Event $event): JsonResponse
    {
        $this->authorizeOrganizer($event);

        if ($event->status === 'published') {
            return response()->json([
                'message' => 'Cannot delete a published event. Please cancel or end the event first.',
            ], 422);
        }

        $event->delete();

        return response()->json(['message' => 'Event deleted successfully.']);
    }

    private function authorizeOrganizer(Event $event): void
    {
        abort_if($event->organizer_id !== Auth::id(), 403, 'You are not the organizer of this event.');
    }

    private function loadRegistrationCount(Event $event): void
    {
        $event->loadCount([
            'registrations as registered_count' => fn ($query) => $query->where('status', 'confirmed'),
        ]);
    }
}
