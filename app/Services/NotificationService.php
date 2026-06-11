<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Notification;

class NotificationService
{
    public function create(int $userId, int $eventId, string $type, string $title, string $message): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'event_id' => $eventId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
        ]);
    }

    public function notifyRegistrationConfirmed(int $userId, Event $event): void
    {
        $this->create(
            $userId,
            $event->id,
            'registration_confirmed',
            'Registration confirmed',
            "You are confirmed for {$event->title}."
        );
    }

    public function notifyRegistrationWaitlisted(int $userId, Event $event): void
    {
        $this->create(
            $userId,
            $event->id,
            'registration_waitlisted',
            'You are on the waitlist',
            "The event {$event->title} is full. You have been added to the waitlist."
        );
    }

    public function notifyWaitlistPromoted(int $userId, int $eventId): void
    {
        $this->create(
            $userId,
            $eventId,
            'waitlist_promoted',
            'You are now confirmed',
            'A spot opened up and your registration has been confirmed.'
        );
    }

    public function notifyEventUpdate(int $userId, Event $event): void
    {
        $this->create(
            $userId,
            $event->id,
            'event_updated',
            'Event updated',
            "The event {$event->title} has been updated."
        );
    }

    public function notifyEventCancellation(int $userId, Event $event): void
    {
        $this->create(
            $userId,
            $event->id,
            'event_cancelled',
            'Event cancelled',
            "The event {$event->title} has been cancelled."
        );
    }

    public function notifyMultipleUsers(array $userIds, Event $event, string $type, string $title, string $message): void
    {
        foreach ($userIds as $userId) {
            $this->create($userId, $event->id, $type, $title, $message);
        }
    }
}
