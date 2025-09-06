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
                        動会
                    </a>
                    <a href="{{ route('attendance.list') }}" class="text-white hover:text-gray-300 transition-colors">
                        動名一覧
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
                    <div class="mb-8">
                        <!-- 退勤・休憩ボタン（横並び） -->
                        <div class="flex justify-center space-x-6">
                            <!-- 退勤ボタン -->
                            <form method="POST" action="{{ route('attendance.clock-out') }}">
                                @csrf
                                <button type="submit" 
                                        class="bg-black text-white px-12 py-4 rounded-md hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-200 font-medium text-lg">
                                    退勤
                                </button>
                            </form>

                            <!-- 休憩入/休憩戻ボタン -->
                            @if(!$attendance->break_start || ($attendance->break_start && $attendance->break_end))
                                <form method="POST" action="{{ route('attendance.break-start') }}">
                                    @csrf
                                    <button type="submit" 
                                            class="bg-black text-white px-12 py-4 rounded-md hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-200 font-medium text-lg">
                                        休憩入
                                    </button>
                                </form>
                            @endif

                            @if($attendance->break_start && !$attendance->break_end)
                                <form method="POST" action="{{ route('attendance.break-end') }}">
                                    @csrf
                                    <button type="submit" 
                                            class="bg-black text-white px-12 py-4 rounded-md hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-200 font-medium text-lg">
                                        休憩戻
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- 退勤後の状態 -->
                @if($attendance && $attendance->clock_out)
                    <div class="p-4 bg-white rounded-lg shadow text-left">
                        <h3 class="text-lg font-semibold mb-4 text-center">本日の勤怠（完了）</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">出勤時刻:</span>
                                <span class="font-medium">{{ $attendance->clock_in->format('H:i') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">退勤時刻:</span>
                                <span class="font-medium">{{ $attendance->clock_out->format('H:i') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">休憩時間:</span>
                                <span class="font-medium">{{ $attendance->formatted_break_time }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">勤務時間:</span>
                                <span class="font-medium">{{ $attendance->formatted_work_time }}</span>
                            </div>
                        </div>
                        <div class="text-center mt-4 text-green-600 font-medium">
                            お疲れ様でした！
                        </div>
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
