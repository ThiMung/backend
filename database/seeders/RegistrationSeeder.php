<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Seeder;

class RegistrationSeeder extends Seeder
{
    public function run(): void
    {
        $events = Event::all();
        $attendees = User::where('role', 'attendee')->get();

        // Event distribution scenarios:
        // 1. Some events not full
        // 2. Some events at capacity (full)
        // 3. Some events with waitlist

        $registrationPlans = [
            1 => ['confirmed' => 15, 'waitlist' => 0],  // AI Summit - Not full (capacity: 20)
            2 => ['confirmed' => 15, 'waitlist' => 5],  // Web Dev - Full with waitlist (capacity: 15)
            3 => ['confirmed' => 18, 'waitlist' => 0],  // Marathon - Not full (capacity: 25)
            4 => ['confirmed' => 10, 'waitlist' => 8],  // Basketball - Full with waitlist (capacity: 10)
            5 => ['confirmed' => 20, 'waitlist' => 3],  // Music Festival - Full with waitlist (capacity: 20)
            6 => ['confirmed' => 12, 'waitlist' => 0],  // Jazz Night - Not full (capacity: 15)
            7 => ['confirmed' => 20, 'waitlist' => 0],  // Art Exhibition - Full, no waitlist (capacity: 20)
            8 => ['confirmed' => 12, 'waitlist' => 4],  // Photography - Full with waitlist (capacity: 12)
            9 => ['confirmed' => 15, 'waitlist' => 0],  // Food & Wine - Not full (capacity: 18)
            10 => ['confirmed' => 10, 'waitlist' => 6], // Cooking Class - Full with waitlist (capacity: 10)
            11 => ['confirmed' => 16, 'waitlist' => 0], // Garden Cleanup - Not full (capacity: 20)
            12 => ['confirmed' => 10, 'waitlist' => 0], // Sustainable Living - Not full (capacity: 15)
        ];

        $attendeeIndex = 0;

        foreach ($events as $event) {
            $plan = $registrationPlans[$event->id] ?? ['confirmed' => 5, 'waitlist' => 0];
            $totalRegistrations = $plan['confirmed'] + $plan['waitlist'];

            for ($i = 0; $i < $totalRegistrations; $i++) {
                if ($attendeeIndex >= $attendees->count()) {
                    break;
                }

                $attendee = $attendees[$attendeeIndex % $attendees->count()];
                
                // Check if already registered
                $exists = Registration::where('event_id', $event->id)
                    ->where('user_id', $attendee->id)
                    ->exists();

                if ($exists) {
                    $attendeeIndex++;
                    continue;
                }

                $isConfirmed = $i < $plan['confirmed'];

                Registration::create([
                    'event_id' => $event->id,
                    'user_id' => $attendee->id,
                    'status' => $isConfirmed ? 'confirmed' : 'waitlist',
                    'position' => $isConfirmed ? null : ($i - $plan['confirmed'] + 1),
                ]);

                $attendeeIndex++;
            }
        }
    }
}
