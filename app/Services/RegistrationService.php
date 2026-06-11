<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;

class RegistrationService
{
    public function __construct(
        private WaitlistService $waitlistService,
        private NotificationService $notificationService
    ) {}

    public function registerUser(int $userId, int $eventId): Registration
    {
        return DB::transaction(function () use ($userId, $eventId) {
            $event = Event::query()
                ->where('status', 'published')
                ->lockForUpdate()
                ->findOrFail($eventId);

            $existing = Registration::query()
                ->where('event_id', $event->id)
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            if ($existing && $existing->status !== 'cancelled') {
                return $existing;
            }

            $confirmedCount = Registration::query()
                ->where('event_id', $event->id)
                ->where('status', 'confirmed')
                ->count();

            $status = $confirmedCount < $event->capacity ? 'confirmed' : 'waitlist';
            $position = $status === 'waitlist' 
                ? $this->waitlistService->calculateNextPosition($event->id) 
                : null;

            $registration = Registration::updateOrCreate(
                [
                    'event_id' => $event->id,
                    'user_id' => $userId,
                ],
                [
                    'status' => $status,
                    'position' => $position,
                ]
            );

            $registration->load('event');

            // Send notification
            if ($status === 'confirmed') {
                $this->notificationService->notifyRegistrationConfirmed($userId, $event);
            } else {
                $this->notificationService->notifyRegistrationWaitlisted($userId, $event);
            }

            return $registration;
        });
    }

    public function cancelRegistration(int $userId, int $eventId): void
    {
        DB::transaction(function () use ($userId, $eventId) {
            $registration = Registration::query()
                ->where('event_id', $eventId)
                ->where('user_id', $userId)
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
                $this->waitlistService->promoteFromWaitlist($eventId);
                return;
            }

            if ($oldStatus === 'waitlist' && $oldPosition !== null) {
                $this->waitlistService->updatePositions($eventId, $oldPosition);
            }
        });
    }
}
