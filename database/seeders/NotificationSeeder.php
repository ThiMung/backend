<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\Registration;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        // Create registration confirmation notifications for confirmed users
        $confirmedRegistrations = Registration::where('status', 'confirmed')
            ->with('event')
            ->limit(15)
            ->get();

        foreach ($confirmedRegistrations as $registration) {
            Notification::create([
                'user_id' => $registration->user_id,
                'event_id' => $registration->event_id,
                'type' => 'registration_confirmed',
                'title' => 'Registration confirmed',
                'message' => "You are confirmed for {$registration->event->title}.",
                'read_at' => rand(0, 1) ? now()->subHours(rand(1, 48)) : null, // 50% read
            ]);
        }

        // Create waitlist notifications for waitlisted users
        $waitlistedRegistrations = Registration::where('status', 'waitlist')
            ->with('event')
            ->limit(10)
            ->get();

        foreach ($waitlistedRegistrations as $registration) {
            Notification::create([
                'user_id' => $registration->user_id,
                'event_id' => $registration->event_id,
                'type' => 'registration_waitlisted',
                'title' => 'You are on the waitlist',
                'message' => "The event {$registration->event->title} is full. You have been added to the waitlist.",
                'read_at' => rand(0, 1) ? now()->subHours(rand(1, 24)) : null,
            ]);
        }
    }
}
