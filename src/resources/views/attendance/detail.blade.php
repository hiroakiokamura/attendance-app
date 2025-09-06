<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>勤怠詳細 - COACHTECH</title>
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
            <!-- タイトル -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-800">勤怠詳細</h1>
            </div>

            <!-- 勤怠詳細フォーム -->
            <div class="max-w-2xl mx-auto">
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <div class="space-y-6">
                        <!-- 名前 -->
                        <div class="flex items-center">
                            <label class="w-24 text-sm font-medium text-gray-700">
                                名前
                            </label>
                            <div class="flex-1 ml-8">
                                <span class="text-gray-900">{{ $attendance->user->name }}</span>
                            </div>
                        </div>

                        <!-- 日付 -->
                        <div class="flex items-center">
                            <label class="w-24 text-sm font-medium text-gray-700">
                                日付
                            </label>
                            <div class="flex-1 ml-8 flex items-center space-x-4">
                                <span class="text-gray-900">{{ $attendance->work_date->format('Y年') }}</span>
                                <span class="text-gray-900">{{ $attendance->work_date->format('n月j日') }}</span>
                            </div>
                        </div>

                        <!-- 出勤・退勤 -->
                        <div class="flex items-center">
                            <label class="w-24 text-sm font-medium text-gray-700">
                                出勤・退勤
                            </label>
                            <div class="flex-1 ml-8 flex items-center space-x-4">
                                <input type="text" 
                                       value="{{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '--:--' }}" 
                                       readonly
                                       class="w-20 px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-center">
                                <span class="text-gray-500">～</span>
                                <input type="text" 
                                       value="{{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '--:--' }}" 
                                       readonly
                                       class="w-20 px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-center">
                            </div>
                        </div>

                        <!-- 休憩 -->
                        <div class="flex items-center">
                            <label class="w-24 text-sm font-medium text-gray-700">
                                休憩
                            </label>
                            <div class="flex-1 ml-8 flex items-center space-x-4">
                                <input type="text" 
                                       value="{{ $attendance->break_start ? $attendance->break_start->format('H:i') : '--:--' }}" 
                                       readonly
                                       class="w-20 px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-center">
                                <span class="text-gray-500">～</span>
                                <input type="text" 
                                       value="{{ $attendance->break_end ? $attendance->break_end->format('H:i') : '--:--' }}" 
                                       readonly
                                       class="w-20 px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-center">
                            </div>
                        </div>

                        <!-- 休憩2 -->
                        <div class="flex items-center">
                            <label class="w-24 text-sm font-medium text-gray-700">
                                休憩2
                            </label>
                            <div class="flex-1 ml-8 flex items-center space-x-4">
                                <input type="text" 
                                       value="--:--" 
                                       readonly
                                       class="w-20 px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-center">
                                <span class="text-gray-500">～</span>
                                <input type="text" 
                                       value="--:--" 
                                       readonly
                                       class="w-20 px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-center">
                            </div>
                        </div>

                        <!-- 備考 -->
                        <div class="flex">
                            <label class="w-24 text-sm font-medium text-gray-700 pt-2">
                                備考
                            </label>
                            <div class="flex-1 ml-8">
                                <textarea name="notes"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md resize-none focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                          rows="3"
                                          placeholder="備考を入力してください">{{ $attendance->notes ?? '電車遅延のため' }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- 修正ボタン -->
                    <div class="mt-8 flex justify-end">
                        <a href="{{ route('attendance.edit', $attendance) }}" 
                           class="bg-black text-white px-8 py-3 rounded-md hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-200 font-medium">
                            修正
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
