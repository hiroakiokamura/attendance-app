<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class EmailVerificationToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'token',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * トークンが有効期限内かどうかを確認
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * 有効期限内のトークンを取得
     */
    public static function validToken(string $email, string $token): ?self
    {
        return self::where('email', $email)
            ->where('token', $token)
            ->where('expires_at', '>', Carbon::now())
            ->first();
    }

    /**
     * 期限切れのトークンを削除
     */
    public static function deleteExpired(): int
    {
        return self::where('expires_at', '<', Carbon::now())->delete();
    }

    /**
     * 指定されたメールアドレスの既存トークンを削除
     */
    public static function deleteByEmail(string $email): int
    {
        return self::where('email', $email)->delete();
    }
}