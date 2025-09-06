<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>勤怠一覧 - COACHTECH</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white min-h-screen">
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
        <!-- タイトル -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800 text-center">勤怠一覧</h1>
        </div>

        <!-- 日付ナビゲーション -->
        <div class="mb-8 flex justify-center">
            <div class="flex items-center space-x-6">
                <a href="{{ route('admin.attendance.list', ['date' => $prevDate]) }}" 
                   class="text-blue-600 hover:text-blue-800 font-medium">
                    ← 前日
                </a>
                
                <span class="text-lg font-medium text-gray-800">
                    {{ $currentDate->format('Y/m/d') }}
                </span>
                
                <a href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}" 
                   class="text-blue-600 hover:text-blue-800 font-medium">
                    翌日 →
                </a>
            </div>
        </div>

        <!-- 勤怠一覧テーブル -->
        <div class="max-w-4xl mx-auto">
            <div class="bg-white border-2 border-blue-400 rounded-lg shadow overflow-hidden">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-center text-sm font-medium text-gray-700">
                                名前
                            </th>
                            <th class="px-6 py-3 text-center text-sm font-medium text-gray-700">
                                出勤
                            </th>
                            <th class="px-6 py-3 text-center text-sm font-medium text-gray-700">
                                退勤
                            </th>
                            <th class="px-6 py-3 text-center text-sm font-medium text-gray-700">
                                休憩
                            </th>
                            <th class="px-6 py-3 text-center text-sm font-medium text-gray-700">
                                合計
                            </th>
                            <th class="px-6 py-3 text-center text-sm font-medium text-gray-700">
                                詳細
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($attendances as $attendance)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-center text-sm text-gray-900">
                                    {{ $attendance->user->name }}
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-900">
                                    {{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '-' }}
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-900">
                                    {{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '-' }}
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-900">
                                    {{ $attendance->formatted_break_time ?: '-' }}
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-900">
                                    {{ $attendance->formatted_work_time ?: '-' }}
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-900">
                                    <a href="{{ route('admin.attendance.detail', $attendance->id) }}" 
                                       class="text-blue-600 hover:text-blue-800 underline">
                                        詳細
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                    この日の勤怠記録がありません
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
