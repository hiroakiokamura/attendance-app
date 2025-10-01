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

                            <!-- 休憩時間 -->
                            <div id="break-times-container">
                                @php
                                    $breakTimes = $attendance->breakTimes ?? collect();
                                    // 既存の休憩時間がない場合は、最低1つの空の休憩時間を表示
                                    if ($breakTimes->isEmpty()) {
                                        $breakTimes = collect([null]);
                                    }
                                @endphp
                                
                                @foreach($breakTimes as $index => $breakTime)
                                    <div class="break-time-row flex items-center" data-break-index="{{ $index }}">
                                        <label class="w-24 text-sm font-medium text-gray-700">
                                            休憩{{ $index + 1 }}
                                        </label>
                                        <div class="flex-1 ml-8 flex items-center space-x-4">
                                            <input type="text" 
                                                   name="break_times[{{ $index }}][start_time]"
                                                   value="{{ old('break_times.'.$index.'.start_time', $breakTime ? $breakTime->start_time->format('H:i') : '') }}" 
                                                   placeholder="12:00"
                                                   class="w-20 px-3 py-2 border border-gray-300 rounded-md text-center focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <span class="text-gray-500">～</span>
                                            <input type="text" 
                                                   name="break_times[{{ $index }}][end_time]"
                                                   value="{{ old('break_times.'.$index.'.end_time', $breakTime ? $breakTime->end_time->format('H:i') : '') }}" 
                                                   placeholder="13:00"
                                                   class="w-20 px-3 py-2 border border-gray-300 rounded-md text-center focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            @if($index > 0)
                                                <button type="button" onclick="removeBreakTime({{ $index }})" 
                                                        class="ml-2 px-2 py-1 text-red-600 hover:text-red-800 text-sm">
                                                    削除
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- 休憩時間追加ボタン -->
                            <div class="flex items-center">
                                <div class="w-24"></div>
                                <div class="flex-1 ml-8">
                                    <button type="button" onclick="addBreakTime()" 
                                            class="px-4 py-2 text-blue-600 hover:text-blue-800 text-sm border border-blue-300 rounded-md hover:bg-blue-50 transition-colors">
                                        + 休憩時間を追加
                                    </button>
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

    <script>
        let breakTimeIndex = {{ $breakTimes->count() }};

        function addBreakTime() {
            const container = document.getElementById('break-times-container');
            const newBreakRow = document.createElement('div');
            newBreakRow.className = 'break-time-row flex items-center';
            newBreakRow.setAttribute('data-break-index', breakTimeIndex);
            
            newBreakRow.innerHTML = `
                <label class="w-24 text-sm font-medium text-gray-700">
                    休憩${breakTimeIndex + 1}
                </label>
                <div class="flex-1 ml-8 flex items-center space-x-4">
                    <input type="text" 
                           name="break_times[${breakTimeIndex}][start_time]"
                           placeholder="12:00"
                           class="w-20 px-3 py-2 border border-gray-300 rounded-md text-center focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <span class="text-gray-500">～</span>
                    <input type="text" 
                           name="break_times[${breakTimeIndex}][end_time]"
                           placeholder="13:00"
                           class="w-20 px-3 py-2 border border-gray-300 rounded-md text-center focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <button type="button" onclick="removeBreakTime(${breakTimeIndex})" 
                            class="ml-2 px-2 py-1 text-red-600 hover:text-red-800 text-sm">
                        削除
                    </button>
                </div>
            `;
            
            container.appendChild(newBreakRow);
            breakTimeIndex++;
        }

        function removeBreakTime(index) {
            const row = document.querySelector(`[data-break-index="${index}"]`);
            if (row) {
                row.remove();
                updateBreakLabels();
            }
        }

        function updateBreakLabels() {
            const rows = document.querySelectorAll('.break-time-row');
            rows.forEach((row, index) => {
                const label = row.querySelector('label');
                if (label) {
                    label.textContent = `休憩${index + 1}`;
                }
            });
        }
    </script>
</body>
</html>

