<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use App\Models\Review;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    // Lấy danh sách đánh giá của 1 sự kiện
    public function index(int $eventId)
    {
        // Lấy danh sách đánh giá kèm theo thông tin user (tên)
        $reviews = Review::with('user:id,name')
            ->where('event_id', $eventId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reviews
        ], 200);
    }

    // Người tham gia gửi đánh giá
    public function store(Request $request, int $eventId)
    {
        // 1. Validate dữ liệu đầu vào
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000' // Sửa thành required để khớp với FE validation
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // 2. Kiểm tra sự kiện có tồn tại không
        $event = Event::find($eventId);
        if (!$event) {
            return response()->json(['success' => false, 'message' => 'Event not found.'], 404);
        }

        // 3. Kiểm tra vòng đời sự kiện (Đã đồng bộ sang cột end_time khớp với Seeder/Frontend của bạn)
        // Lưu ý: Nếu DB của bạn dùng tên cột khác (như end_date), hãy giữ nguyên. 
        // Nhưng nếu seeder dùng end_time, phải đổi ở đây thành end_time để tránh lỗi không tìm thấy cột.
        $eventEndTime = $event->end_time ?? $event->end_date; 
        if ($event->status !== 'ended' || ($eventEndTime && Carbon::parse($eventEndTime)->isFuture())) {
            return response()->json(['success' => false, 'message' => 'You can only review an event after it has ended.'], 403);
        }

        $userId = $request->user()->id;

        // 4. Kiểm tra user đã tham gia sự kiện chưa (trạng thái đăng ký phải là confirmed)
        $hasAttended = Registration::where('event_id', $eventId)
            ->where('user_id', $userId)
            ->where('status', 'confirmed') // Chỉ user "confirmed" mới được đánh giá
            ->exists();

        if (!$hasAttended) {
            return response()->json(['success' => false, 'message' => 'You have not successfully attended this event, so you cannot review it.'], 403);
        }

        // 5. Kiểm tra user đã đánh giá sự kiện này chưa (Mỗi người 1 lần)
        $alreadyReviewed = Review::where('event_id', $eventId)->where('user_id', $userId)->exists();
        if ($alreadyReviewed) {
            return response()->json(['success' => false, 'message' => 'You have already reviewed this event.'], 409);
        }

        // 6. Lưu đánh giá vào DB
        $review = Review::create([
            'user_id' => $userId,
            'event_id' => $eventId,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        // =========================================================
        // Nạp thông tin User lập tức để frontend hiển thị tên reviewer.
        // =========================================================
        $review->load(['user' => function($query) {
            $query->select('id', 'name');
        }]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you for reviewing the event!',
            'data' => $review // Trả về object đã nhuộm đủ thông tin user.name phục vụ render SPA
        ], 201);
    }
}