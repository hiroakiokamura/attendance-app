<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // ユーザーがログインしていない場合はそのまま通す
        if (!$user) {
            return $next($request);
        }

        // メールアドレスが認証済みの場合はそのまま通す
        if ($user->email_verified_at) {
            return $next($request);
        }

        // メールアドレスが未認証の場合は認証画面にリダイレクト
        return redirect()->route('email.verify.show');
    }
}