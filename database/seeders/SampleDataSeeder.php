<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Registration;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // --- 1. TẠO 50 TÀI KHOẢN NGƯỜI THAM GIA (ATTENDEE) ---
        // Tạo vòng lặp để sinh ra danh sách tài khoản test tính năng hàng đợi và đăng ký
        $attendees = collect();
        for ($i = 1; $i <= 50; $i++) {
            $attendees->push(User::create([
                'name' => "Attendee {$i}",
                'email' => "attendee{$i}@test.com",
                'password' => Hash::make('123456'), // Mật khẩu chung dễ nhớ dùng để đăng nhập chạy thử
                'role' => 'attendee',
            ]));
        }

        // --- 2. TẠO TÀI KHOẢN NHÀ TỔ CHỨC (ORGANIZER) THEO ĐÚNG VAI TRÒ ---
        // Mỗi đơn vị tổ chức sẽ đại diện quản lý một nhóm chuyên biệt
        $organizers = [
            'Local Arts Council' => User::create([
                'name' => 'Local Arts Council',
                'email' => 'organizer@test.com',
                'password' => Hash::make('123456'),
                'role' => 'organizer',
            ]),
            'Sports Club' => User::create([
                'name' => 'Sports Club',
                'email' => 'sports@test.com',
                'password' => Hash::make('123456'),
                'role' => 'organizer',
            ]),
            'Culinary Society' => User::create([
                'name' => 'Culinary Society',
                'email' => 'food@test.com',
                'password' => Hash::make('123456'),
                'role' => 'organizer',
            ]),
            'Art Collective' => User::create([
                'name' => 'Art Collective',
                'email' => 'art@test.com',
                'password' => Hash::make('123456'),
                'role' => 'organizer',
            ]),
            'Tech Hub' => User::create([
                'name' => 'Tech Hub',
                'email' => 'tech@test.com',
                'password' => Hash::make('123456'),
                'role' => 'organizer',
            ]),
            'Green Initiative' => User::create([
                'name' => 'Green Initiative',
                'email' => 'green@test.com',
                'password' => Hash::make('123456'),
                'role' => 'organizer',
            ]),
        ];

        // --- 3. KHỞI TẠO MẢNG 12 SỰ KIỆN MẪU PHÂN BỔ ĐỀU CHO 6 CATEGORIES ---
        // Đảm bảo mỗi category xuất hiện chính xác 2 lần với ảnh Unsplash chất lượng cao
        $eventsData = [
            // --- CẶP DANH MỤC: MUSIC ---
            [
                'title' => 'Summer Music Festival 2024',
                'description' => 'Đêm nhạc hội bùng nổ cùng các nghệ sĩ Indie và Rock hàng đầu.',
                'location' => 'Công viên trung tâm bờ hồ',
                'category' => 'Music',
                'capacity' => 50,
                'registered' => 45, // Đăng ký ít hơn sức chứa -> Trạng thái: confirmed
                'rating' => 5,
                'image_url' => 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?q=80&w=800',
                'organizer_name' => 'Local Arts Council'
            ],
            [
                'title' => 'Jazz Night Live Concert',
                'description' => 'Không gian nhạc Jazz cổ điển đầy lãng mạn cho những buổi hẹn hò cuối tuần.',
                'location' => 'Phòng trà Blue Note Hall',
                'category' => 'Music',
                'capacity' => 30,
                'registered' => 20,
                'rating' => 5,
                'image_url' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?q=80&w=800',
                'organizer_name' => 'Local Arts Council'
            ],

            // --- CẶP DANH MỤC: SPORTS ---
            [
                'title' => 'City Marathon 2024',
                'description' => 'Giải chạy việt dã thường niên nâng cao sức khỏe cộng đồng cư dân.',
                'location' => 'Khu đô thị Downtown',
                'category' => 'Sports',
                'capacity' => 40,
                'registered' => 30,
                'rating' => 5,
                'image_url' => 'https://images.unsplash.com/photo-1452626038303-6effd6b0b974?q=80&w=800',
                'organizer_name' => 'Sports Club'
            ],
            [
                'title' => '3-on-3 Basketball Tournament',
                'description' => 'Giải đấu bóng rổ đường phố kịch tính với sự tranh tài của 16 đội mạnh nhất.',
                'location' => 'Sân thể thao phức hợp',
                'category' => 'Sports',
                'capacity' => 15,
                'registered' => 22, // VƯỢT QUÁ CAPACITY (15) -> Sẽ tự động sinh 7 người vào hàng đợi waitlist
                'rating' => 4,
                'image_url' => 'https://images.unsplash.com/photo-1546519638-68e109498ffc?q=80&w=800',
                'organizer_name' => 'Sports Club'
            ],

            // --- CẶP DANH MỤC: FOOD & DRINK ---
            [
                'title' => 'Food & Wine Festival',
                'description' => 'Trải nghiệm hành trình ẩm thực năm châu và thử rượu vang hảo hạng thượng vị.',
                'location' => 'Quảng trường Riverside Plaza',
                'category' => 'Food & Drink',
                'capacity' => 20,
                'registered' => 25, // VƯỢT QUÁ CAPACITY (20) -> Sẽ tự động sinh 5 người vào hàng đợi waitlist
                'rating' => 5,
                'image_url' => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?q=80&w=800',
                'organizer_name' => 'Culinary Society'
            ],
            [
                'title' => 'Gourmet Italian Cooking Class',
                'description' => 'Khóa học chế biến mì Ý sốt tươi chuẩn vị nhà hàng Michelin cùng bếp trưởng.',
                'location' => 'Studio Ẩm Thực CookUp',
                'category' => 'Food & Drink',
                'capacity' => 12,
                'registered' => 10,
                'rating' => 5,
                'image_url' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?q=80&w=800',
                'organizer_name' => 'Culinary Society'
            ],

            // --- CẶP DANH MỤC: ARTS ---
            [
                'title' => 'Modern Art Exhibition',
                'description' => 'Không gian trưng bày tranh nghệ thuật đương đại mang tính trừu tượng số học.',
                'location' => 'Triển lãm Art Gallery',
                'category' => 'Arts',
                'capacity' => 40,
                'registered' => 35,
                'rating' => 5,
                'image_url' => 'https://images.unsplash.com/photo-1460661419201-fd4cecdf50ca?q=80&w=800',
                'organizer_name' => 'Art Collective'
            ],
            [
                'title' => 'Canvas Oil Painting Workshop',
                'description' => 'Lớp học vẽ tranh sơn dầu trên nền vải Canvas dành cho mọi lứa tuổi bắt đầu.',
                'location' => 'Trung tâm Creative Studio',
                'category' => 'Arts',
                'capacity' => 15,
                'registered' => 12,
                'rating' => 5,
                'image_url' => 'https://images.unsplash.com/photo-1513364776144-60967b0f800f?q=80&w=800',
                'organizer_name' => 'Art Collective'
            ],

            // --- CẶP DANH MỤC: EDUCATION ---
            [
                'title' => 'Tech Conference & AI Trends 2024',
                'description' => 'Hội thảo chia sẻ kiến thức chuyên sâu về Mô hình ngôn ngữ lớn LLM và Trí tuệ nhân tạo.',
                'location' => 'Trung tâm hội nghị Convention Center',
                'category' => 'Education',
                'capacity' => 100,
                'registered' => 80,
                'rating' => 4,
                'image_url' => 'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?q=80&w=800',
                'organizer_name' => 'Tech Hub'
            ],
            [
                'title' => 'Photography Basics Masterclass',
                'description' => 'Nắm vững kỹ năng kiểm soát khẩu độ, tốc độ màn trập và bố cục ánh sáng nhiếp ảnh.',
                'location' => 'Phòng Studio số 5 TechHub',
                'category' => 'Education',
                'capacity' => 20,
                'registered' => 15,
                'rating' => 5,
                'image_url' => 'https://images.unsplash.com/photo-1452587925148-ce544e77e70d?q=80&w=800',
                'organizer_name' => 'Tech Hub'
            ],

            // --- CẶP DANH MỤC: COMMUNITY ---
            [
                'title' => 'Community Cleanup Day',
                'description' => 'Hành động nhỏ ý nghĩa lớn - Chung tay dọn dẹp và thu gom rác thải bãi biển xanh sạch.',
                'location' => 'Khuôn viên Công viên Thành Phố',
                'category' => 'Community',
                'capacity' => 50,
                'registered' => 40,
                'rating' => 5,
                'image_url' => 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?q=80&w=800',
                'organizer_name' => 'Green Initiative'
            ],
            [
                'title' => 'Green Living Eco Seminar',
                'description' => 'Tọa đàm hướng dẫn phân loại rác tại nguồn và lối sống xanh giảm phát thải nhựa.',
                'location' => 'Hội trường Thảo Xanh',
                'category' => 'Community',
                'capacity' => 25,
                'registered' => 32, // VƯỢT QUÁ CAPACITY (25) -> Sẽ tự động sinh 7 người vào hàng đợi waitlist
                'rating' => 5,
                'image_url' => 'https://images.unsplash.com/photo-1559027615-cd4628902d4a?q=80&w=800',
                'organizer_name' => 'Green Initiative'
            ],
        ];

        // --- 4. TIẾN HÀNH DUYỆT QUA MẢNG VÀ LƯU VÀO CƠ SỞ DỮ LIỆU ---
        foreach ($eventsData as $index => $data) {
            $org = $organizers[$data['organizer_name']];

            // Khởi tạo Event mẫu
            $event = Event::create([
                'organizer_id' => $org->id,
                'title' => $data['title'],
                'description' => $data['description'],
                'location' => $data['location'],
                'image_url' => $data['image_url'],
                'category' => $data['category'],
                'start_time' => now()->addDays(7 + $index),
                'end_time' => now()->addDays(7 + $index)->addHours(4),
                'capacity' => $data['capacity'],
                'status' => 'published', // Đặt trạng thái ban đầu là đã xuất bản theo yêu cầu nghiệp vụ
            ]);

            // Lấy lượng attendee ra để thực hiện logic phân chia hàng đợi
            $regCount = min($data['registered'], $attendees->count());
            $eventAttendees = $attendees->take($regCount);

            $count = 0; // Biến đếm thứ tự đăng ký của sự kiện hiện tại

            foreach ($eventAttendees as $attendee) {
                $count++;

                // Áp dụng chặt chẽ nghiệp vụ quy định bởi giảng viên:
                // Nếu lượt đăng ký hiện hành vượt quá sức chứa (capacity) của sự kiện đó
                if ($count > $event->capacity) {
                    $status = 'waitlist';
                    $position = $count - $event->capacity; // Tính số thứ tự chờ tăng dần: 1, 2, 3...
                } else {
                    $status = 'confirmed';
                    $position = null; // Đăng ký thành công hợp lệ không có số vị trí đợi
                }

                // Lưu bản ghi vào bảng registrations
                Registration::create([
                    'event_id' => $event->id,
                    'user_id' => $attendee->id,
                    'status' => $status,
                    'position' => $position,
                ]);

                // Chỉ tạo Review (Đánh giá) mẫu cho những thành viên đã được 'confirmed'
                if ($status === 'confirmed') {
                    Review::create([
                        'event_id' => $event->id,
                        'user_id' => $attendee->id,
                        'rating' => $data['rating'],
                        'comment' => 'Sự kiện tổ chức rất tuyệt vời, giảng viên hướng dẫn cực kỳ tâm huyết!',
                    ]);
                }
            }
        }
    }
}