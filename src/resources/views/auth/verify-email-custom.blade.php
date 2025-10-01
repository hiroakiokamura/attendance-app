<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メール認証 - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- ヘッダー -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <!-- Atteの文字を削除 -->
                </div>
            </div>
        </div>
    </header>

    <!-- メインコンテンツ -->
    <div class="flex items-center justify-center min-h-screen py-12">
        <div class="w-full max-w-md">
            <!-- 成功メッセージ -->
            @if (session('success'))
                <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded text-center text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <!-- メール認証メッセージ -->
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">メール認証</h2>
                <p class="text-gray-800 text-sm leading-relaxed">
                    登録していただいたメールアドレスに認証メールを送信しました。<br>
                    メール内のリンクをクリックして認証を完了してください。
                </p>
            </div>

            <!-- 認証メール再送信メッセージ -->
            @if (session('status') == 'verification-link-sent')
                <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded text-center text-sm">
                    新しい認証リンクをメールアドレスに送信しました。
                </div>
            @endif

            <!-- 認証メール再送信フォーム -->
            <div class="text-center mb-8">
                <form method="POST" action="{{ route('email.verify.resend') }}">
                    @csrf
                    <button type="submit" 
                            class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-md font-medium transition duration-300 shadow-md hover:shadow-lg">
                        認証メールを再送信
                    </button>
                </form>
            </div>

            <!-- ログアウトリンク -->
            <div class="text-center">
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" 
                            class="text-gray-600 hover:text-gray-800 text-sm underline transition duration-300">
                        ログアウト
                    </button>
                </form>
            </div>

            <!-- MailHog情報（開発環境用） -->
            @if (config('app.env') === 'local')
                <div class="mt-8 p-4 bg-yellow-100 border border-yellow-400 text-yellow-800 rounded text-sm">
                    <p class="font-semibold mb-2">開発環境での確認方法：</p>
                    <p>MailHogでメールを確認できます：</p>
                    <a href="http://localhost:8025" target="_blank" 
                       class="text-blue-600 hover:text-blue-800 underline">
                        http://localhost:8025
                    </a>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
