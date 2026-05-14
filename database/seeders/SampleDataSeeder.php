<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Event;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo 1 tài khoản Organizer mẫu
        $organizer = User::create([
            'name' => 'Ban To Chuc A',
            'email' => 'organizer@test.com',
            'password' => Hash::make('123456'),
        'role' => 'organizer',
    ]);

    // 2. Tạo 1 tài khoản Attendee mẫu
    User::create([
        'name' => 'Nguoi Tham Gia B',
        'email' => 'user@test.com',
        'password' => Hash::make('123456'),
        'role' => 'attendee',
    ]);

    // 3. Tạo 1 sự kiện mẫu
    Event::create([
        'organizer_id' => $organizer->id,
        'title' => 'Hội thảo Web Laravel 13',
        'description' => 'Tìm hiểu về các tính năng mới nhất của Laravel',
        'location' => 'Hội trường A1',
        'start_time' => now()->addDays(7),
        'end_time' => now()->addDays(7)->addHours(3),
        'capacity' => 50,
        'status' => 'published',
    ]);
}
}