<?php

namespace Database\Seeders;

use App\Models\Registration;
use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $confirmedRegistrations = Registration::where('status', 'confirmed')
            ->with('event')
            ->get();

        $comments = [
            'Amazing event! Really enjoyed the experience and learned a lot.',
            'Well organized and the venue was perfect. Highly recommend!',
            'Great atmosphere and friendly people. Will definitely come again.',
            'Exceeded my expectations. The organizers did an excellent job.',
            'Good event overall, but could improve on timing.',
            'Fantastic experience! Worth every minute.',
            'Very informative and engaging. Thanks to the organizers!',
            'Nice event, met interesting people and had fun.',
            'Could be better, but still enjoyable.',
            'Outstanding! One of the best events I\'ve attended.',
        ];

        // Create reviews for about 60% of confirmed registrations
        $reviewCount = (int) ceil($confirmedRegistrations->count() * 0.6);
        $registrationsToReview = $confirmedRegistrations->random(min($reviewCount, $confirmedRegistrations->count()));

        foreach ($registrationsToReview as $registration) {
            Review::create([
                'event_id' => $registration->event_id,
                'user_id' => $registration->user_id,
                'rating' => rand(3, 5), // Ratings between 3 and 5
                'comment' => $comments[array_rand($comments)],
            ]);
        }
    }
}
