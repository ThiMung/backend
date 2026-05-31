<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Notification;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OrganizerEventController extends Controller
{
    private const DEFAULT_EVENT_IMAGE = 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?q=80&w=800';

    private const EVENT_CATEGORIES = [
        'Music',
        'Sports',
        'Food & Drink',
        'Arts',
        'Education',
        'Community',
    ];

    private const ALLOWED_STATUS_TRANSITIONS = [
        'draft' => ['published', 'cancelled'],
        'published' => ['cancelled', 'ended'],
        'cancelled' => [],
        'ended' => [],
    ];

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

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateEvent($request);
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
        $this->loadRegistrationCount($event);

        return response()->json(['event' => $event]);
    }

    public function update(Request $request, Event $event): JsonResponse
    {
        $this->authorizeOrganizer($event);

        $data = $this->validateEvent($request);
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

        $this->notifyRegisteredAttendees(
            $event,
            'event_updated',
            'Event updated',
            "The event {$event->title} has been updated.",
        );

        $this->loadRegistrationCount($event);

        return response()->json([
            'message' => 'Event updated successfully.',
            'event' => $event,
        ]);
    }

    public function updateStatus(Request $request, Event $event): JsonResponse
    {
        $this->authorizeOrganizer($event);

        $data = $request->validate([
            'status' => ['required', Rule::in(['published', 'cancelled', 'ended'])],
        ]);

        $currentStatus = $event->status;
        $nextStatus = $data['status'];

        // Vòng đời chỉ đi tới: draft -> published -> cancelled/ended.
        if (!in_array($nextStatus, self::ALLOWED_STATUS_TRANSITIONS[$currentStatus] ?? [], true)) {
            return response()->json([
                'message' => 'Không thể chuyển trạng thái sự kiện theo chiều ngược hoặc từ trạng thái đã khóa.',
            ], 422);
        }

        $event->update(['status' => $nextStatus]);

        if ($nextStatus === 'cancelled') {
            $this->notifyRegisteredAttendees(
                $event,
                'event_cancelled',
                'Event cancelled',
                "The event {$event->title} has been cancelled.",
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
                'message' => 'Không xóa sự kiện đã published. Hãy chuyển sang cancelled hoặc ended.',
            ], 422);
        }

        $event->delete();

        return response()->json(['message' => 'Event deleted successfully.']);
    }

    private function validateEvent(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(self::EVENT_CATEGORIES)],
            'start_time' => ['required', 'date', 'after:now'],
            'capacity' => ['required', 'integer', 'min:1'],
            'image_url' => ['nullable', 'url'],
        ]);
    }

    private function authorizeOrganizer(Event $event): void
    {
        abort_if($event->organizer_id !== Auth::id(), 403, 'Bạn không có quyền quản lý sự kiện này.');
    }

    private function loadRegistrationCount(Event $event): void
    {
        $event->loadCount([
            'registrations as registered_count' => fn ($query) => $query->where('status', 'confirmed'),
        ]);
    }

    private function notifyRegisteredAttendees(
        Event $event,
        string $type,
        string $title,
        string $message,
    ): void {
        $userIds = Registration::query()
            ->where('event_id', $event->id)
            ->whereIn('status', ['confirmed', 'waitlist'])
            ->pluck('user_id');

        $userIds->each(function (int $userId) use ($event, $type, $title, $message) {
            Notification::create([
                'user_id' => $userId,
                'event_id' => $event->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
            ]);
        });
    }
}
