<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrganizerEventController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{id}', [EventController::class, 'show']);

Route::prefix('organizer')->group(function () {
    Route::post('/register', [AuthController::class, 'registerOrganizer']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::prefix('attendee')->group(function () {
    Route::post('/register', [AuthController::class, 'registerAttendee']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {
    // Organizer chỉ quản lý sự kiện thuộc tài khoản đang đăng nhập.
    Route::middleware('role:organizer')->prefix('organizer')->group(function () {
        Route::get('/events', [OrganizerEventController::class, 'index']);
        Route::post('/events', [OrganizerEventController::class, 'store']);
        Route::get('/events/{event}', [OrganizerEventController::class, 'show']);
        Route::put('/events/{event}', [OrganizerEventController::class, 'update']);
        Route::patch('/events/{event}/status', [OrganizerEventController::class, 'updateStatus']);
        Route::delete('/events/{event}', [OrganizerEventController::class, 'destroy']);
    });

    Route::middleware('role:attendee')->prefix('attendee')->group(function () {
        Route::post('/registrations', [RegistrationController::class, 'store']);
        Route::get('/my-registrations', [RegistrationController::class, 'myRegistrations']);
        Route::delete('/registrations/{event}', [RegistrationController::class, 'destroy']);
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    });
});
