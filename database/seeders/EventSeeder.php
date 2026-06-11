<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $organizers = User::where('role', 'organizer')->get()->keyBy('email');

        $events = [
            [
                'organizer_email' => 'organizer@test.com',
                'title' => 'AI & Machine Learning Summit 2024',
                'description' => 'Join industry experts discussing the latest trends in artificial intelligence and machine learning.',
                'location' => 'Tech Convention Center',
                'category' => 'Education',
                'capacity' => 20,
                'image_url' => 'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?q=80&w=800',
                'status' => 'published',
                'days_ahead' => 10,
            ],
            [
                'organizer_email' => 'organizer@test.com',
                'title' => 'Web Development Bootcamp',
                'description' => 'Intensive hands-on workshop covering React, Node.js, and modern web technologies.',
                'location' => 'Downtown Innovation Hub',
                'category' => 'Education',
                'capacity' => 15,
                'image_url' => 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?q=80&w=800',
                'status' => 'published',
                'days_ahead' => 7,
            ],
            [
                'organizer_email' => 'sports@test.com',
                'title' => 'City Marathon 2024',
                'description' => 'Annual city-wide marathon promoting health and fitness in our community.',
                'location' => 'Central Park',
                'category' => 'Sports',
                'capacity' => 25,
                'image_url' => 'https://images.unsplash.com/photo-1452626038306-9aae5e071dd3?q=80&w=800',
                'status' => 'published',
                'days_ahead' => 14,
            ],
            [
                'organizer_email' => 'sports@test.com',
                'title' => 'Basketball Tournament Finals',
                'description' => 'Exciting 3-on-3 basketball championship with prizes for top teams.',
                'location' => 'Community Sports Complex',
                'category' => 'Sports',
                'capacity' => 10,
                'image_url' => 'https://images.unsplash.com/photo-1546519638-68e109498ffc?q=80&w=800',
                'status' => 'published',
                'days_ahead' => 5,
            ],
            [
                'organizer_email' => 'arts@test.com',
                'title' => 'Summer Music Festival',
                'description' => 'Live performances from local bands and indie artists under the stars.',
                'location' => 'Riverside Amphitheater',
                'category' => 'Music',
                'capacity' => 20,
                'image_url' => 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?q=80&w=800',
                'status' => 'published',
                'days_ahead' => 12,
            ],
            [
                'organizer_email' => 'arts@test.com',
                'title' => 'Jazz Night at Blue Note',
                'description' => 'Intimate evening of smooth jazz with acclaimed musicians.',
                'location' => 'Blue Note Jazz Club',
                'category' => 'Music',
                'capacity' => 15,
                'image_url' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?q=80&w=800',
                'status' => 'published',
                'days_ahead' => 8,
            ],
            [
                'organizer_email' => 'arts@test.com',
                'title' => 'Modern Art Exhibition',
                'description' => 'Showcasing contemporary works from emerging local artists.',
                'location' => 'City Art Gallery',
                'category' => 'Arts',
                'capacity' => 20,
                'image_url' => 'https://images.unsplash.com/photo-1460661419201-fd4cecdf50ca?q=80&w=800',
                'status' => 'published',
                'days_ahead' => 6,
            ],
            [
                'organizer_email' => 'arts@test.com',
                'title' => 'Photography Workshop',
                'description' => 'Learn professional photography techniques from award-winning photographers.',
                'location' => 'Studio Arts Center',
                'category' => 'Arts',
                'capacity' => 12,
                'image_url' => 'https://images.unsplash.com/photo-1452587925148-ce544e77e70d?q=80&w=800',
                'status' => 'published',
                'days_ahead' => 9,
            ],
            [
                'organizer_email' => 'organizer@test.com',
                'title' => 'Food & Wine Tasting Experience',
                'description' => 'Culinary journey featuring gourmet dishes paired with fine wines.',
                'location' => 'Grand Hotel Ballroom',
                'category' => 'Food & Drink',
                'capacity' => 18,
                'image_url' => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?q=80&w=800',
                'status' => 'published',
                'days_ahead' => 11,
            ],
            [
                'organizer_email' => 'organizer@test.com',
                'title' => 'Italian Cooking Masterclass',
                'description' => 'Hands-on class learning authentic Italian recipes from a master chef.',
                'location' => 'Culinary Institute',
                'category' => 'Food & Drink',
                'capacity' => 10,
                'image_url' => 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?q=80&w=800',
                'status' => 'published',
                'days_ahead' => 4,
            ],
            [
                'organizer_email' => 'sports@test.com',
                'title' => 'Community Garden Cleanup',
                'description' => 'Join us in beautifying our neighborhood parks and gardens.',
                'location' => 'Oakwood Community Park',
                'category' => 'Community',
                'capacity' => 20,
                'image_url' => 'https://images.unsplash.com/photo-1593113598332-cd288d649433?q=80&w=800',
                'status' => 'published',
                'days_ahead' => 3,
            ],
            [
                'organizer_email' => 'sports@test.com',
                'title' => 'Sustainable Living Workshop',
                'description' => 'Learn practical tips for reducing waste and living eco-friendly.',
                'location' => 'Green Community Center',
                'category' => 'Community',
                'capacity' => 15,
                'image_url' => 'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?q=80&w=800',
                'status' => 'published',
                'days_ahead' => 13,
            ],
        ];

        foreach ($events as $eventData) {
            $organizer = $organizers[$eventData['organizer_email']];
            $startTime = now()->addDays($eventData['days_ahead']);

            Event::create([
                'organizer_id' => $organizer->id,
                'title' => $eventData['title'],
                'description' => $eventData['description'],
                'location' => $eventData['location'],
                'category' => $eventData['category'],
                'capacity' => $eventData['capacity'],
                'image_url' => $eventData['image_url'],
                'status' => $eventData['status'],
                'start_time' => $startTime,
                'end_time' => $startTime->copy()->addHours(3),
            ]);
        }
    }
}
