<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Các trường được phép lưu dữ liệu hàng loạt (Mass Assignment)
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Trường này để phân biệt Organizer/Attendee
    ];

    /**
     * Các trường sẽ bị ẩn khi trả về dữ liệu JSON cho React
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Ép kiểu dữ liệu (Casting)
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}