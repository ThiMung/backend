<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create 3 Organizers
        $organizers = [
            ['name' => 'Tech Hub Events', 'email' => 'organizer@test.com'],
            ['name' => 'City Sports Association', 'email' => 'sports@test.com'],
            ['name' => 'Arts & Culture Foundation', 'email' => 'arts@test.com'],
        ];

        foreach ($organizers as $organizer) {
            User::create([
                'name' => $organizer['name'],
                'email' => $organizer['email'],
                'password' => Hash::make('123456'),
                'role' => 'organizer',
            ]);
        }

        // Create 30 Realistic Attendees
        $attendees = [
            ['name' => 'Alice Johnson', 'email' => 'alice.johnson@test.com'],
            ['name' => 'Bob Smith', 'email' => 'bob.smith@test.com'],
            ['name' => 'Charlie Brown', 'email' => 'charlie.brown@test.com'],
            ['name' => 'Diana Prince', 'email' => 'diana.prince@test.com'],
            ['name' => 'Ethan Hunt', 'email' => 'ethan.hunt@test.com'],
            ['name' => 'Fiona Green', 'email' => 'fiona.green@test.com'],
            ['name' => 'George Wilson', 'email' => 'george.wilson@test.com'],
            ['name' => 'Hannah Lee', 'email' => 'hannah.lee@test.com'],
            ['name' => 'Isaac Newton', 'email' => 'isaac.newton@test.com'],
            ['name' => 'Julia Roberts', 'email' => 'julia.roberts@test.com'],
            ['name' => 'Kevin Hart', 'email' => 'kevin.hart@test.com'],
            ['name' => 'Laura Palmer', 'email' => 'laura.palmer@test.com'],
            ['name' => 'Michael Scott', 'email' => 'michael.scott@test.com'],
            ['name' => 'Nina Williams', 'email' => 'nina.williams@test.com'],
            ['name' => 'Oliver Queen', 'email' => 'oliver.queen@test.com'],
            ['name' => 'Pamela Anderson', 'email' => 'pamela.anderson@test.com'],
            ['name' => 'Quincy Adams', 'email' => 'quincy.adams@test.com'],
            ['name' => 'Rachel Green', 'email' => 'rachel.green@test.com'],
            ['name' => 'Samuel Jackson', 'email' => 'samuel.jackson@test.com'],
            ['name' => 'Tina Turner', 'email' => 'tina.turner@test.com'],
            ['name' => 'Uma Thurman', 'email' => 'uma.thurman@test.com'],
            ['name' => 'Victor Stone', 'email' => 'victor.stone@test.com'],
            ['name' => 'Wendy Davis', 'email' => 'wendy.davis@test.com'],
            ['name' => 'Xavier Charles', 'email' => 'xavier.charles@test.com'],
            ['name' => 'Yara Martinez', 'email' => 'yara.martinez@test.com'],
            ['name' => 'Zachary Taylor', 'email' => 'zachary.taylor@test.com'],
            ['name' => 'Amelia Clarke', 'email' => 'amelia.clarke@test.com'],
            ['name' => 'Benjamin Franklin', 'email' => 'benjamin.franklin@test.com'],
            ['name' => 'Catherine Zeta', 'email' => 'catherine.zeta@test.com'],
            ['name' => 'David Tennant', 'email' => 'david.tennant@test.com'],
        ];

        foreach ($attendees as $attendee) {
            User::create([
                'name' => $attendee['name'],
                'email' => $attendee['email'],
                'password' => Hash::make('123456'),
                'role' => 'attendee',
            ]);
        }
    }
}
