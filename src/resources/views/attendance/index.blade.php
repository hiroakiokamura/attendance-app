<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>勤怠登録 - COACHTECH</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- ヘッダー -->
    <header class="bg-black text-white py-4">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between">
                <!-- COACHTECHロゴ -->
                <div class="flex items-center">
                    <img src="{{ asset('images/logos/coachtech-logo.svg') }}" 
                         alt="COACHTECH" 
                         class="h-8 w-auto">
                </div>
                
                <!-- ナビゲーション -->
                <nav class="flex items-center space-x-6">
                    <a href="{{ route('attendance.index') }}" class="text-white hover:text-gray-300 transition-colors">
                        勤怠
                    </a>
                    <a href="{{ route('attendance.list') }}" class="text-white hover:text-gray-300 transition-colors">
                        勤怠一覧
                    </a>
                    <a href="{{ route('stamp_correction_request.list') }}" class="text-white hover:text-gray-300 transition-colors">
                        申請
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-white hover:text-gray-300 transition-colors">
                            ログアウト
                        </button>
                    </form>
                </nav>
            </div>
        </div>
    </header>

    <!-- メインコンテンツ -->
    <div class="bg-gray-100 min-h-screen" style="min-height: calc(100vh - 80px);">
        <div class="container mx-auto px-4 py-8">
            <!-- アラートメッセージ -->
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded max-w-md mx-auto">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded max-w-md mx-auto">
                    {{ session('error') }}
                </div>
            @endif

            <!-- メインコンテンツエリア -->
            <div class="max-w-md mx-auto text-center">
                <!-- ユーザー挨拶 -->
                <div class="mb-6">
                    <p class="text-lg text-gray-700">
                        {{ Auth::user()->name }}さんお疲れ様です！
                    </p>
                </div>

                <!-- 日付表示 -->
                <div class="mb-8">
                    <p class="text-xl text-gray-800 font-medium">
                        {{ now()->format('Y年n月j日（D）') }}
                    </p>
                </div>

                <!-- 時刻表示 -->
                <div class="mb-12">
                    <div class="text-6xl font-bold text-gray-800" id="currentTime">
                        {{ now()->format('H:i') }}
                    </div>
                </div>

                <!-- 出勤ボタン（出勤前の状態） -->
                @if(!$attendance || !$attendance->clock_in)
                    <div class="mb-8">
                        <form method="POST" action="{{ route('attendance.clock-in') }}">
                            @csrf
                            <button type="submit" 
                                    class="bg-black text-white px-16 py-4 rounded-md hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-200 font-medium text-lg">
                                出勤
                            </button>
                        </form>
                    </div>
                @endif

                <!-- 出勤後の状態（退勤・休憩ボタン表示） -->
                @if($attendance && $attendance->clock_in && !$attendance->clock_out)
                    <!-- 休憩中の状態：休憩戻ボタンのみ表示 -->
                    @if($attendance->break_start && !$attendance->break_end)
                        <div class="mb-8">
                            <form method="POST" action="{{ route('attendance.break-end') }}">
                                @csrf
                                <button type="submit" 
                                        class="bg-black text-white px-16 py-4 rounded-md hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-200 font-medium text-lg">
                                    休憩戻
                                </button>
                            </form>
                        </div>
                    @else
                        <!-- 出勤後（休憩前）の状態：退勤・休憩入ボタン横並び -->
                        <div class="mb-8">
                            <div class="flex justify-center space-x-6">
                                <!-- 退勤ボタン -->
                                <form method="POST" action="{{ route('attendance.clock-out') }}">
                                    @csrf
                                    <button type="submit" 
                                            class="bg-black text-white px-12 py-4 rounded-md hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-200 font-medium text-lg">
                                        退勤
                                    </button>
                                </form>

                                <!-- 休憩入ボタン -->
                                <form method="POST" action="{{ route('attendance.break-start') }}">
                                    @csrf
                                    <button type="submit" 
                                            class="bg-black text-white px-12 py-4 rounded-md hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-200 font-medium text-lg">
                                        休憩入
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                @endif

                <!-- 退勤後の状態 -->
                @if($attendance && $attendance->clock_out)
                    <div class="mb-8">
                        <p class="text-lg text-gray-800 font-medium text-center">
                            お疲れ様でした。
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        // 現在時刻を1分ごとに更新（秒は表示しないため）
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('ja-JP', { 
                hour: '2-digit', 
                minute: '2-digit'
            });
            document.getElementById('currentTime').textContent = timeString;
        }

        setInterval(updateTime, 60000); // 1分ごと
    </script>
</body>
</html>
