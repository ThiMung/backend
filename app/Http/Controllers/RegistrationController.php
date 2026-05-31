<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Notification;
use App\Models\Registration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RegistrationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
        ]);

        [$registration, $shouldNotify] = DB::transaction(function () use ($data) {
            $event = Event::query()
                ->where('status', 'published')
                ->lockForUpdate()
                ->findOrFail($data['event_id']);

            $existing = Registration::query()
                ->where('event_id', $event->id)
                ->where('user_id', Auth::id())
                ->lockForUpdate()
                ->first();

            if ($existing && $existing->status !== 'cancelled') {
                return [$existing, false];
            }

            $confirmedCount = Registration::query()
                ->where('event_id', $event->id)
                ->where('status', 'confirmed')
                ->count();

            $status = $confirmedCount < $event->capacity ? 'confirmed' : 'waitlist';
            $position = $status === 'waitlist' ? $this->nextWaitlistPosition($event->id) : null;

            $registration = Registration::updateOrCreate(
                [
                    'event_id' => $event->id,
                    'user_id' => Auth::id(),
                ],
                [
                    'status' => $status,
                    'position' => $position,
                ],
            );

            return [$registration->load('event'), true];
        });

        if ($shouldNotify) {
            $this->notifyRegistrationResult($registration);
        }

        return response()->json([
            'message' => $registration->status === 'confirmed'
                ? 'Registration confirmed.'
                : 'Event is full. You have been added to the waitlist.',
            'registration' => $registration,
        ], 201);
    }

    public function myRegistrations(): JsonResponse
    {
        $registrations = Registration::query()
            ->with('event')
            ->where('user_id', Auth::id())
            ->where('status', '!=', 'cancelled')
            ->latest()
            ->get();

        return response()->json($registrations);
    }

    public function destroy(Event $event): JsonResponse
    {
        DB::transaction(function () use ($event) {
            $registration = Registration::query()
                ->where('event_id', $event->id)
                ->where('user_id', Auth::id())
                ->lockForUpdate()
                ->firstOrFail();

            if ($registration->status === 'cancelled') {
                return;
            }

            $oldStatus = $registration->status;
            $oldPosition = $registration->position;

            $registration->update([
                'status' => 'cancelled',
                'position' => null,
            ]);

            if ($oldStatus === 'confirmed') {
                $this->promoteFirstWaitlistUser($event->id);
                return;
            }

            if ($oldStatus === 'waitlist' && $oldPosition !== null) {
                $this->moveWaitlistForward($event->id, $oldPosition);
            }
        });

        return response()->json(['message' => 'Registration cancelled successfully.']);
    }

    private function nextWaitlistPosition(int $eventId): int
    {
        $lastPosition = Registration::query()
            ->where('event_id', $eventId)
            ->where('status', 'waitlist')
            ->max('position');

        return ((int) $lastPosition) + 1;
    }

    private function promoteFirstWaitlistUser(int $eventId): void
    {
        // Khi 1 confirmed hủy, người đầu hàng đợi được đẩy lên và các vị trí sau giảm 1.
        $nextRegistration = Registration::query()
            ->where('event_id', $eventId)
            ->where('status', 'waitlist')
            ->where('position', 1)
            ->lockForUpdate()
            ->first();

        if (!$nextRegistration) {
            return;
        }

        $nextRegistration->update([
            'status' => 'confirmed',
            'position' => null,
        ]);

        $this->createNotification(
            $nextRegistration->user_id,
            $eventId,
            'waitlist_promoted',
            'You are now confirmed',
            'A spot opened up and your registration has been confirmed.',
        );

        $this->moveWaitlistForward($eventId, 1);
    }

    private function moveWaitlistForward(int $eventId, int $fromPosition): void
    {
        Registration::query()
            ->where('event_id', $eventId)
            ->where('status', 'waitlist')
            ->where('position', '>', $fromPosition)
            ->decrement('position');
    }

    private function notifyRegistrationResult(Registration $registration): void
    {
        $event = $registration->event;

        if ($registration->status === 'confirmed') {
            $this->createNotification(
                $registration->user_id,
                $registration->event_id,
                'registration_confirmed',
                'Registration confirmed',
                "You are confirmed for {$event->title}.",
            );

            return;
        }

        $this->createNotification(
            $registration->user_id,
            $registration->event_id,
            'registration_waitlisted',
            'You are on the waitlist',
            "The event {$event->title} is full. You have been added to the waitlist.",
        );
    }

    private function createNotification(
        int $userId,
        int $eventId,
        string $type,
        string $title,
        string $message,
    ): void {
        Notification::create([
            'user_id' => $userId,
            'event_id' => $eventId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
        ]);
    }
}
