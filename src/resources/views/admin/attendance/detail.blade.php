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

                <!-- ナビゲーションメニュー -->
                <nav class="flex space-x-6">
                    <a href="{{ route('admin.attendance.list') }}" 
                       class="text-white hover:text-gray-300 transition-colors {{ request()->routeIs('admin.attendance.*') ? 'border-b-2 border-white' : '' }}">
                        勤怠一覧
                    </a>
                    <a href="{{ route('admin.staff.list') }}" 
                       class="text-white hover:text-gray-300 transition-colors {{ request()->routeIs('admin.staff.*') ? 'border-b-2 border-white' : '' }}">
                        スタッフ一覧
                    </a>
                    <a href="{{ route('admin.stamp_correction_request.list') }}" 
                       class="text-white hover:text-gray-300 transition-colors {{ request()->routeIs('admin.stamp_correction_request.*') ? 'border-b-2 border-white' : '' }}">
                        申請一覧
                    </a>
                    <form method="POST" action="{{ route('admin.logout') }}" class="inline">
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
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- タイトル -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-800">勤怠詳細</h1>
            </div>

            <!-- 成功メッセージ -->
            @if (session('success'))
                <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded max-w-2xl mx-auto">
                    {{ session('success') }}
                </div>
            @endif

            <!-- エラーメッセージ -->
            @if($errors->any())
                <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded max-w-2xl mx-auto">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <!-- 勤怠詳細フォーム -->
            <div class="max-w-2xl mx-auto">
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <form method="POST" action="{{ route('admin.attendance.update', $attendance->id) }}">
                        @csrf
                        @method('PUT')
                        
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
                                <div class="flex-1 ml-8 flex space-x-4">
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
                                           name="clock_in"
                                           value="{{ old('clock_in', $attendance->clock_in ? $attendance->clock_in->format('H:i') : '') }}" 
                                           placeholder="09:00"
                                           class="w-20 px-3 py-2 border border-gray-300 rounded-md text-center focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <span class="text-gray-500">～</span>
                                    <input type="text" 
                                           name="clock_out"
                                           value="{{ old('clock_out', $attendance->clock_out ? $attendance->clock_out->format('H:i') : '') }}" 
                                           placeholder="20:00"
                                           class="w-20 px-3 py-2 border border-gray-300 rounded-md text-center focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>

                            <!-- 休憩 -->
                            <div class="flex items-center">
                                <label class="w-24 text-sm font-medium text-gray-700">
                                    休憩
                                </label>
                                <div class="flex-1 ml-8 flex items-center space-x-4">
                                    <input type="text" 
                                           name="break_start"
                                           value="{{ old('break_start', $attendance->break_start ? $attendance->break_start->format('H:i') : '') }}" 
                                           placeholder="12:00"
                                           class="w-20 px-3 py-2 border border-gray-300 rounded-md text-center focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <span class="text-gray-500">～</span>
                                    <input type="text" 
                                           name="break_end"
                                           value="{{ old('break_end', $attendance->break_end ? $attendance->break_end->format('H:i') : '') }}" 
                                           placeholder="13:00"
                                           class="w-20 px-3 py-2 border border-gray-300 rounded-md text-center focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>

                            <!-- 休憩2 -->
                            <div class="flex items-center">
                                <label class="w-24 text-sm font-medium text-gray-700">
                                    休憩2
                                </label>
                                <div class="flex-1 ml-8 flex items-center space-x-4">
                                    <input type="text" 
                                           name="break2_start"
                                           value="{{ old('break2_start', '') }}" 
                                           placeholder="15:00"
                                           class="w-20 px-3 py-2 border border-gray-300 rounded-md text-center focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <span class="text-gray-500">～</span>
                                    <input type="text" 
                                           name="break2_end"
                                           value="{{ old('break2_end', '') }}" 
                                           placeholder="15:15"
                                           class="w-20 px-3 py-2 border border-gray-300 rounded-md text-center focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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
                                              placeholder="備考を入力してください">{{ old('notes', $attendance->notes ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- 修正ボタン -->
                        <div class="mt-8 flex justify-end">
                            <button type="submit" 
                                    class="bg-black text-white px-8 py-3 rounded-md hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-200 font-medium">
                                修正
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

