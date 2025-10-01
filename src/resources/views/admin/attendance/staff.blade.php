<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $user->name }}さんの勤怠 - COACHTECH</title>
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
                <nav class="flex items-center space-x-8">
                    <a href="{{ route('admin.attendance.list') }}" 
                       class="text-white hover:text-gray-300 transition-colors">
                        勤怠一覧
                    </a>
                    <a href="{{ route('admin.staff.list') }}" 
                       class="text-white hover:text-gray-300 transition-colors">
                        スタッフ一覧
                    </a>
                    <a href="{{ route('admin.stamp_correction_request.list') }}" 
                       class="text-white hover:text-gray-300 transition-colors">
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
            <h1 class="text-2xl font-bold text-gray-800 border-l-4 border-black pl-4">
                {{ $user->name }}さんの勤怠
            </h1>
        </div>

        <!-- 月選択 -->
        <div class="flex items-center justify-center mb-8 space-x-6">
            <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $prevMonth]) }}" 
               class="text-blue-600 hover:text-blue-800 transition-colors">
                ← 前月
            </a>
            <span class="text-xl font-semibold text-gray-800">
                {{ Carbon\Carbon::parse($currentMonth.'-01')->format('Y/m') }}
            </span>
            <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $nextMonth]) }}" 
               class="text-blue-600 hover:text-blue-800 transition-colors">
                翌月 →
            </a>
        </div>

        <!-- 勤怠一覧テーブル -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-medium text-gray-700">
                            日付
                        </th>
                        <th class="px-6 py-4 text-left text-sm font-medium text-gray-700">
                            出勤
                        </th>
                        <th class="px-6 py-4 text-left text-sm font-medium text-gray-700">
                            退勤
                        </th>
                        <th class="px-6 py-4 text-left text-sm font-medium text-gray-700">
                            休憩
                        </th>
                        <th class="px-6 py-4 text-left text-sm font-medium text-gray-700">
                            勤務
                        </th>
                        <th class="px-6 py-4 text-left text-sm font-medium text-gray-700">
                            詳細
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @php
                        // 指定月の全日付を生成
                        $startDate = Carbon\Carbon::parse($currentMonth.'-01');
                        $endDate = $startDate->copy()->endOfMonth();
                        $attendancesByDate = $attendances->keyBy(function($item) {
                            return $item->work_date->format('Y-m-d');
                        });
                    @endphp
                    
                    @for($date = $startDate->copy(); $date->lte($endDate); $date->addDay())
                        @php
                            $dateStr = $date->format('Y-m-d');
                            $attendance = $attendancesByDate->get($dateStr);
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $date->format('m/d') }}({{ $date->format('D') }})
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $attendance && $attendance->clock_in ? $attendance->clock_in->format('H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $attendance && $attendance->clock_out ? $attendance->clock_out->format('H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $attendance ? $attendance->formatted_break_time : '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $attendance ? $attendance->formatted_work_time : '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($attendance)
                                    <a href="{{ route('admin.attendance.detail', $attendance->id) }}" 
                                       class="text-blue-600 hover:text-blue-800 transition-colors">
                                        詳細
                                    </a>
                                @else
                                    <span class="text-gray-400">詳細</span>
                                @endif
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <!-- CSV出力ボタン -->
        <div class="flex justify-end">
            <form method="GET" action="{{ route('admin.attendance.staff', $user->id) }}">
                <input type="hidden" name="month" value="{{ $currentMonth }}">
                <input type="hidden" name="format" value="csv">
                <button type="submit" 
                        class="bg-black hover:bg-gray-800 text-white px-6 py-2 rounded text-sm font-medium transition-colors">
                    CSV出力
                </button>
            </form>
        </div>
    </div>
</body>
</html>