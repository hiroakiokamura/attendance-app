<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ログインしているかチェック
        if (!auth()->check()) {
            return redirect()->route('admin.login');
        }

        // 管理者かどうかチェック
        if (!auth()->user()->is_admin) {
            abort(403, 'アクセス権限がありません。');
        }

        return $next($request);
    }
}
