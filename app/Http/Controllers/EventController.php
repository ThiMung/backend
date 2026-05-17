<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::query()
            ->where('status', 'published')
            ->with('organizer:id,name')
            ->withCount([
                'registrations as registered_count' => fn ($q) => $q->where('status', 'confirmed'),
            ])
            ->withAvg('reviews', 'rating');

        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhereHas('organizer', fn ($oq) => $oq->where('name', 'like', "%{$search}%"));
            });
        }

        $events = $query->orderBy('start_time')->get()->map(fn ($event) => $this->formatEvent($event));

        $categories = Event::where('status', '=', 'published', 'and')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->values();

        return response()->json([
            'events' => $events,
            'categories' => $categories,
        ]);
    }

    public function show(string $id)
    {
        $event = Event::query()
            ->where('status', 'published')
            ->with('organizer:id,name')
            ->withCount([
                'registrations as registered_count' => fn ($q) => $q->where('status', 'confirmed'),
            ])
            ->withAvg('reviews', 'rating')
            ->findOrFail($id);

        return response()->json($this->formatEvent($event));
    }

    private function formatEvent(Event $event): array
    {
        $registered = (int) ($event->registered_count ?? 0);
        $spotsLeft = max(0, $event->capacity - $registered);

        return [
            'id' => $event->id,
            'organizer_id' => $event->organizer_id,
            'title' => $event->title,
            'description' => $event->description,
            'location' => $event->location,
            'image_url' => $event->image_url,
            'category' => $event->category,
            'start_time' => $event->start_time,
            'end_time' => $event->end_time,
            'capacity' => $event->capacity,
            'status' => $event->status,
            'organizer_name' => $event->organizer?->name,
            'registered_count' => $registered,
            'spots_left' => $spotsLeft,
            'is_full' => $spotsLeft === 0,
            'average_rating' => $event->reviews_avg_rating
                ? round((float) $event->reviews_avg_rating, 1)
                : null,
        ];
    }

    // Chức năng lấy danh sách sự kiện của 1 Organizer đang đăng nhập
    public function getOrganizerEvents() {
        $events = Event::where('organizer_id', Auth::id()) // Lọc chính xác theo ID từ token đăng nhập
            ->withCount(['registrations as registered_count' => fn ($q) => $q->where('status', 'confirmed')])
            ->orderBy('start_time')
            ->get();

        return response()->json(['events' => $events]);
    }
    
    // Chức năng tạo sự kiện mới (dành cho Organizer)
    public function store(Request $request)
    {
        // 1. Validate dữ liệu đầu vào nghiêm ngặt
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'location'    => 'required|string|max:255',
            'category'    => 'required|in:Music,Sports,Food & Drink,Arts,Education,Community',
            'start_time'  => 'required|date|after:now',
            'capacity'    => 'required|integer|min:1',
            'image_url'   => 'nullable|url', // Nhận link ảnh trực tiếp từ Cloud do Frontend đẩy lên
        ]);

        // 2. Tự động tính toán end_time mặc định (bằng start_time + 3 tiếng) để không lỗi DB
        $startTime = Carbon::parse($data['start_time']);
        $endTime = $startTime->copy()->addHours(3);

        // 3. Tiến hành lưu thông tin vào bảng events
        $event = Event::create([
            'organizer_id' => Auth::id(), // Lấy ID của Organizer đang đăng nhập qua Sanctum Token
            'title'        => $data['title'],
            'description'  => $data['description'],
            'location'     => $data['location'],
            'category'     => $data['category'],
            'start_time'   => $startTime,
            'end_time'     => $endTime,
            'capacity'     => $data['capacity'],
            'image_url'    => $data['image_url'] ?? 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?q=80&w=800', // Ảnh fallback nếu không upload
            'status'       => 'draft', // Luôn mặc định là draft theo yêu cầu nghiệp vụ
        ]);

        return response()->json([
            'message' => 'Event created successfully as draft!',
            'event'   => $event
        ], 201);
    }
}
