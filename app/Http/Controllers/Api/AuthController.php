<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    private const ROLES = ['organizer', 'attendee'];

    public function registerOrganizer(Request $request): JsonResponse
    {
        return $this->registerWithRole($request, 'organizer');
    }

    public function registerAttendee(Request $request): JsonResponse
    {
        return $this->registerWithRole($request, 'attendee');
    }

     public function googleLogin(Request $request): JsonResponse
    {
        $data = $request->validate(['credential' => ['required', 'string']]);

        // Verify access_token với Google
        $response = Http::get('https://www.googleapis.com/oauth2/v3/userinfo', [
            'access_token' => $data['credential'],
        ]);

        if ($response->failed()) {
            return response()->json(['message' => 'Google token không hợp lệ'], 401);
        }

        $payload = $response->json();

        if (empty($payload['email'])) {
            return response()->json(['message' => 'Không lấy được email từ Google'], 401);
        }

        $user = User::firstOrCreate(
            ['email' => $payload['email']],
            [
                'name' => $payload['name'] ?? $payload['email'],
                'google_id' => $payload['sub'],
                'role' => 'attendee',
                'email_verified_at' => now(),
            ]
        );

        if (!$user->google_id) {
            $user->update(['google_id' => $payload['sub']]);
        }

        if ($user->role !== 'attendee') {
            return response()->json(['message' => 'Bạn không có quyền truy cập vào cổng này'], 403);
        }

        return $this->respondWithToken($user);
    }

    public function login(Request $request): JsonResponse
     {
         $data = $request->validate([
             'email' => ['required', 'email'],
             'password' => ['required'],
             'required_role' => ['required', Rule::in(self::ROLES)],
         ]);
 
         $user = User::where('email', $data['email'])->first();

         if (!$user || !Hash::check($data['password'], $user->password)) {
             return response()->json(['message' => 'Thông tin đăng nhập không chính xác'], 401);
         }

        // Chặn đăng nhập nhầm giữa cổng Organizer và Attendee
         if ($user->role !== $data['required_role']) {
             return response()->json(['message' => 'Bạn không có quyền truy cập vào cổng này'], 403);
         }

         return $this->respondWithToken($user);
     }

    private function registerWithRole(Request $request, string $role): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $role,
        ]);

        return $this->respondWithToken($user);
    }

    private function respondWithToken(User $user): JsonResponse
    {
        return response()->json([
            'token' => $user->createToken('auth_token')->plainTextToken,
            'user' => $user,
        ]);
    }
}
