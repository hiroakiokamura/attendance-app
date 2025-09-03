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
        <!-- タイトルと日付ナビゲーション -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-800">{{ $currentDate->format('Y年m月d日') }}の勤怠</h1>
                
                <!-- 日付ナビゲーション -->
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.attendance.list', ['date' => $prevDate]) }}" 
                       class="flex items-center px-4 py-2 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                        ← 前日
                    </a>
                    
                    <div class="flex items-center px-4 py-2 bg-white border border-gray-300 rounded">
                        📅 {{ $currentDate->format('Y/m/d') }}
                    </div>
                    
                    <a href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}" 
                       class="flex items-center px-4 py-2 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                        翌日 →
                    </a>
                </div>
            </div>
        </div>

        <!-- 勤怠一覧テーブル -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            名前
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            出勤
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            退勤
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            休憩
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            合計
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            詳細
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($attendances as $attendance)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $attendance->user->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '09:00' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '18:00' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $attendance->formatted_break_time ?: '1:00' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $attendance->formatted_work_time ?: '8:00' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <a href="{{ route('admin.attendance.detail', $attendance->id) }}" 
                                   class="bg-gray-100 hover:bg-gray-200 px-3 py-1 rounded text-gray-700 transition-colors">
                                    詳細
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                この日の勤怠記録がありません
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
