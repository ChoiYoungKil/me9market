<?php

namespace App\Models;

// 이메일 인증을 위한 MustVerifyEmail 인터페이스 사용 (현재 사용 안 함)
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// Laravel Passport의 HasApiTokens 트레이트 사용 (Sanctum과는 다름) // https://laravel.com/docs/9.x/passport

class User extends Authenticatable
{
    // Laravel Passport의 HasApiTokens 트레이트 추가
    use /* HasApiTokens, */ HasFactory, Notifiable, \Laravel\Passport\HasApiTokens; 

    /**
     * 대량 할당 가능한 속성
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'mobile',
        'gender',
        'status',
        'address',
        'city',
        'state',
        'country',
        'pincode',
    ];

    /**
     * 직렬화 시 숨겨야 할 속성
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * 캐스트해야 할 속성
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}