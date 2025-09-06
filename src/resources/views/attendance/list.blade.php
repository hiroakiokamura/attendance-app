<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>勤怠一覧 - COACHTECH</title>
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
    <div class="bg-white min-h-screen" style="min-height: calc(100vh - 80px);">
        <div class="container mx-auto px-4 py-8">
            <!-- タイトル -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-800">勤怠一覧</h1>
            </div>

            <!-- 月ナビゲーション -->
            <div class="flex items-center justify-center mb-8 space-x-6">
                <a href="{{ route('attendance.list', ['month' => $prevMonth]) }}" 
                   class="text-blue-600 hover:text-blue-800 transition-colors">
                    ← 前月
                </a>
                <span class="text-lg font-medium text-gray-800">
                    {{ Carbon\Carbon::parse($currentMonth.'-01')->format('Y/m') }}
                </span>
                <a href="{{ route('attendance.list', ['month' => $nextMonth]) }}" 
                   class="text-blue-600 hover:text-blue-800 transition-colors">
                    翌月 →
                </a>
            </div>

            <!-- 勤怠一覧テーブル -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-center text-sm font-medium text-gray-700">
                                日付
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
                                    {{ $attendance->work_date->format('m/d(D)') }}
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-900">
                                    {{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '--:--' }}
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-900">
                                    {{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '--:--' }}
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-900">
                                    {{ $attendance->formatted_break_time }}
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-900">
                                    {{ $attendance->formatted_work_time }}
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-900">
                                    <a href="{{ route('attendance.detail', $attendance->id) }}" 
                                       class="text-blue-600 hover:text-blue-800 underline">
                                        詳細
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                    勤怠記録がありません
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- ページネーション -->
            @if($attendances->hasPages())
                <div class="mt-8 flex justify-center">
                    {{ $attendances->links() }}
                </div>
            @endif
        </div>
    </div>
</body>
</html>
