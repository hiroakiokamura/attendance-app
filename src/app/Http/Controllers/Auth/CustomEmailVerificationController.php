<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerificationToken;
use App\Models\User;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CustomEmailVerificationController extends Controller
{
    /**
     * メール認証待ち画面を表示
     */
    public function show()
    {
        $user = Auth::user();
        
        if ($user && $user->email_verified_at) {
            return redirect()->route('attendance.index');
        }

        return view('auth.verify-email-custom');
    }

    /**
     * 認証メールを再送信
     */
    public function resend(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->email_verified_at) {
            return redirect()->route('attendance.index');
        }

        // 既存のトークンを削除
        EmailVerificationToken::deleteByEmail($user->email);

        // 新しいトークンを生成
        $token = Str::random(64);
        $expiresAt = Carbon::now()->addHours(24);

        EmailVerificationToken::create([
            'email' => $user->email,
            'token' => $token,
            'expires_at' => $expiresAt,
        ]);

        // 認証URLを生成
        $verificationUrl = route('email.verify.custom', [
            'email' => $user->email,
            'token' => $token,
        ]);

        // 認証メールを送信
        $user->notify(new EmailVerificationNotification($verificationUrl));

        return redirect()->route('email.verify.show')->with('status', 'verification-link-sent');
    }

    /**
     * メールアドレスを認証
     */
    public function verify(Request $request, $email, $token)
    {
        if (!$email || !$token) {
            return redirect()->route('login')->with('error', '無効な認証リンクです。');
        }

        // トークンの検証
        $verificationToken = EmailVerificationToken::validToken($email, $token);
        
        if (!$verificationToken) {
            return redirect()->route('login')->with('error', '認証リンクが無効または期限切れです。');
        }

        // ユーザーを取得
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'ユーザーが見つかりません。');
        }

        // メールアドレスを認証済みに更新
        $user->email_verified_at = Carbon::now();
        $user->save();

        // 使用済みトークンを削除
        $verificationToken->delete();

        // 期限切れトークンをクリーンアップ
        EmailVerificationToken::deleteExpired();

        // ユーザーをログイン
        Auth::login($user);

        return redirect()->route('attendance.index')->with('success', 'メールアドレスの認証が完了しました。');
    }

    /**
     * 認証メールを送信（会員登録時に呼び出される）
     */
    public static function sendVerificationEmail(User $user): void
    {
        // 既存のトークンを削除
        EmailVerificationToken::deleteByEmail($user->email);

        // 新しいトークンを生成
        $token = Str::random(64);
        $expiresAt = Carbon::now()->addHours(24);

        EmailVerificationToken::create([
            'email' => $user->email,
            'token' => $token,
            'expires_at' => $expiresAt,
        ]);

        // 認証URLを生成
        $verificationUrl = route('email.verify.custom', [
            'email' => $user->email,
            'token' => $token,
        ]);

        // 認証メールを送信
        $user->notify(new EmailVerificationNotification($verificationUrl));
    }
}