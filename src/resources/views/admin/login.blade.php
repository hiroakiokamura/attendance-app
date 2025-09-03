<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>管理者ログイン - COACHTECH</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- ヘッダー -->
    <header class="bg-black text-white py-4">
        <div class="container mx-auto px-4">
            <div class="flex items-center">
                <!-- COACHTECHロゴ -->
                <div class="flex items-center">
                    <img src="{{ asset('images/logos/coachtech-logo.svg') }}" 
                         alt="COACHTECH" 
                         class="h-8 w-auto"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <!-- フォールバック用ロゴ（SVGが読み込めない場合） -->
                    <div class="items-center" style="display: none;">
                        <div class="bg-white text-black px-2 py-1 rounded mr-2 font-bold text-sm">
                            CT
                        </div>
                        <span class="text-xl font-bold">COACHTECH</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- メインコンテンツ -->
    <div class="flex items-center justify-center min-h-screen bg-gray-100" style="min-height: calc(100vh - 80px);">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <!-- タイトル -->
                <h1 class="text-2xl font-bold text-center text-gray-800 mb-8">管理者ログイン</h1>

                <!-- エラーメッセージ -->
                @if (session('error'))
                    <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- ログインフォーム -->
                <form method="POST" action="{{ route('admin.login.submit') }}">
                    @csrf

                    <!-- メールアドレス -->
                    <div class="mb-6">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            メールアドレス
                        </label>
                        <input id="email" 
                               type="email" 
                               name="email" 
                               value="{{ old('email') }}"
                               required 
                               autofocus 
                               autocomplete="username"
                               class="w-full px-3 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- パスワード -->
                    <div class="mb-8">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            パスワード
                        </label>
                        <input id="password" 
                               type="password" 
                               name="password" 
                               required 
                               autocomplete="current-password"
                               class="w-full px-3 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- ログインボタン -->
                    <button type="submit" 
                            class="w-full bg-black text-white py-3 px-4 rounded-md hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-200 font-medium">
                        管理者ログインする
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
