<?php

namespace App\Services;

use App\Models\Registration;

class WaitlistService
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function calculateNextPosition(int $eventId): int
    {
        $lastPosition = Registration::query()
            ->where('event_id', $eventId)
            ->where('status', 'waitlist')
            ->max('position');

        return ((int) $lastPosition) + 1;
    }

    public function promoteFromWaitlist(int $eventId): void
    {
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

        $this->notificationService->notifyWaitlistPromoted(
            $nextRegistration->user_id,
            $eventId
        );

        $this->updatePositions($eventId, 1);
    }

    public function updatePositions(int $eventId, int $fromPosition): void
    {
        Registration::query()
            ->where('event_id', $eventId)
            ->where('status', 'waitlist')
            ->where('position', '>', $fromPosition)
            ->decrement('position');
    }

    public function getWaitlistCount(int $eventId): int
    {
        return Registration::query()
            ->where('event_id', $eventId)
            ->where('status', 'waitlist')
            ->count();
    }
}
