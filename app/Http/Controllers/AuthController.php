<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Đăng ký cho vai trò Organizer
    public function registerOrganizer(Request $request) {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8'
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'organizer' // Tự động gán quyền organizer 
        ]);

        return response()->json([
            'token' => $user->createToken('auth_token')->plainTextToken,
            'user' => $user
        ]);
    }

    // Đăng ký cho vai trò Attendee
    public function registerAttendee(Request $request) {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8'
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'attendee' // Tự động gán quyền attendee 
        ]);

        return response()->json([
            'token' => $user->createToken('auth_token')->plainTextToken,
            'user' => $user
        ]);
    }

    // Hàm login chung có kiểm tra chéo vai trò
    public function login(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'required_role' => 'required|in:organizer,attendee' // Frontend gửi kèm vai trò yêu cầu 
        ]);

        $user = User::where('email', '=', $request->email, 'and')->first();

        // Kiểm tra tài khoản và mật khẩu
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Thông tin đăng nhập không chính xác'], 401);
        }

        // CHẶN CHÉO: Nếu tài khoản không đúng role yêu cầu từ cổng đăng nhập đó
        if ($user->role !== $request->required_role) {
            return response()->json(['message' => 'Bạn không có quyền truy cập vào cổng này'], 403);
        }

        return response()->json([
            'token' => $user->createToken('auth_token')->plainTextToken,
            'user' => $user
        ]);
    }
}
