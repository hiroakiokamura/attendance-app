<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>メール認証 - COACHTECH</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white min-h-screen">
    <!-- ヘッダー -->
    <header class="bg-black text-white py-4">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-center">
                <!-- COACHTECHロゴ -->
                <div class="flex items-center">
                    <img src="{{ asset('images/logos/coachtech-logo.svg') }}" 
                         alt="COACHTECH" 
                         class="h-8 w-auto">
                </div>
            </div>
        </div>
    </header>

    <!-- メインコンテンツ -->
    <div class="flex items-center justify-center min-h-screen py-12">
        <div class="w-full max-w-md">
            <!-- メール認証メッセージ -->
            <div class="text-center mb-8">
                <p class="text-gray-800 text-sm leading-relaxed">
                    登録していただいたメールアドレスに認証メールを送信しました。<br>
                    メール認証を完了してください。
                </p>
            </div>

            <!-- 成功メッセージ -->
            @if (session('status') == 'verification-link-sent')
                <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded text-center text-sm">
                    新しい認証リンクをメールアドレスに送信しました。
                </div>
            @endif

            <!-- 認証ボタン -->
            <div class="text-center mb-8">
                <button class="bg-gray-400 text-white px-8 py-3 rounded-md font-medium cursor-not-allowed" disabled>
                    認証はこちらから
                </button>
            </div>

            <!-- 認証メール再送リンク -->
            <div class="text-center">
                <form method="POST" action="{{ route('verification.send') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-blue-500 hover:text-blue-700 text-sm underline">
                        認証メールを再送する
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
