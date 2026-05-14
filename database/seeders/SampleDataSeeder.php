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

    // 3. Tạo sự kiện mẫu với link ảnh demo
    $images = [
        'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?q=80&w=800'
    ];

    foreach ($images as $key => $url) {
        Event::create([
            'organizer_id' => $organizer->id,
            'title' => "Sự kiện công nghệ số ",
            'description' => "Mô tả chi tiết cho sự kiện hấp dẫn này...",
            'location' => "Hội trường A1",
            'image_url' => $url, // Dùng link ảnh demo
            'category' => 'Education',
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(7)->addHours(4),
            'capacity' => 50,
            'status' => 'published'
        ]);
    }
}
}