<?php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;

// Nhóm công khai cho Organizer
Route::prefix('organizer')->group(function () {
    Route::post('/register', [AuthController::class, 'registerOrganizer']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Nhóm công khai cho Attendee
Route::prefix('attendee')->group(function () {
    Route::post('/register', [AuthController::class, 'registerAttendee']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Nhóm API yêu cầu đăng nhập (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    
    // API dành riêng cho Organizer 
    Route::middleware('role:organizer')->prefix('organizer')->group(function () {
        // API tạo sự kiện (nhận image_url từ Cloudinary do FE gửi lên) 
        Route::post('/events', [EventController::class, 'store']);
    });

    // API dành riêng cho Attendee 
    Route::middleware('role:attendee')->prefix('attendee')->group(function () {
        // Các route cho người tham gia viết ở đây
    });
});