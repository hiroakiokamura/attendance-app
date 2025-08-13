<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            勤怠登録
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- アラートメッセージ -->
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <!-- 現在の日時表示 -->
                    <div class="text-center mb-8">
                        <div class="text-3xl font-bold text-gray-800 mb-2" id="currentTime">
                            {{ now()->format('H:i:s') }}
                        </div>
                        <div class="text-lg text-gray-600">
                            {{ now()->format('Y年m月d日 (D)') }}
                        </div>
                    </div>

                    <!-- 出勤・退勤ボタン -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <!-- 出勤ボタン -->
                        <div class="text-center">
                            <form method="POST" action="{{ route('attendance.clock-in') }}">
                                @csrf
                                <button type="submit" 
                                        class="btn btn-primary w-full py-8 text-xl {{ $attendance && $attendance->clock_in ? 'opacity-50 cursor-not-allowed' : '' }}"
                                        {{ $attendance && $attendance->clock_in ? 'disabled' : '' }}>
                                    出勤
                                </button>
                            </form>
                            @if($attendance && $attendance->clock_in)
                                <p class="text-sm text-gray-600 mt-2">
                                    出勤時刻: {{ $attendance->clock_in->format('H:i') }}
                                </p>
                            @endif
                        </div>

                        <!-- 退勤ボタン -->
                        <div class="text-center">
                            <form method="POST" action="{{ route('attendance.clock-out') }}">
                                @csrf
                                <button type="submit" 
                                        class="btn btn-danger w-full py-8 text-xl {{ !$attendance || !$attendance->clock_in || $attendance->clock_out ? 'opacity-50 cursor-not-allowed' : '' }}"
                                        {{ !$attendance || !$attendance->clock_in || $attendance->clock_out ? 'disabled' : '' }}>
                                    退勤
                                </button>
                            </form>
                            @if($attendance && $attendance->clock_out)
                                <p class="text-sm text-gray-600 mt-2">
                                    退勤時刻: {{ $attendance->clock_out->format('H:i') }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- 休憩ボタン -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <!-- 休憩開始ボタン -->
                        <div class="text-center">
                            <form method="POST" action="{{ route('attendance.break-start') }}">
                                @csrf
                                <button type="submit" 
                                        class="btn btn-secondary w-full py-6 text-lg {{ !$attendance || !$attendance->clock_in || ($attendance->break_start && !$attendance->break_end) ? 'opacity-50 cursor-not-allowed' : '' }}"
                                        {{ !$attendance || !$attendance->clock_in || ($attendance->break_start && !$attendance->break_end) ? 'disabled' : '' }}>
                                    休憩開始
                                </button>
                            </form>
                            @if($attendance && $attendance->break_start && !$attendance->break_end)
                                <p class="text-sm text-gray-600 mt-2">
                                    休憩開始: {{ $attendance->break_start->format('H:i') }}
                                </p>
                            @endif
                        </div>

                        <!-- 休憩終了ボタン -->
                        <div class="text-center">
                            <form method="POST" action="{{ route('attendance.break-end') }}">
                                @csrf
                                <button type="submit" 
                                        class="btn btn-success w-full py-6 text-lg {{ !$attendance || !$attendance->break_start || $attendance->break_end ? 'opacity-50 cursor-not-allowed' : '' }}"
                                        {{ !$attendance || !$attendance->break_start || $attendance->break_end ? 'disabled' : '' }}>
                                    休憩終了
                                </button>
                            </form>
                            @if($attendance && $attendance->break_end)
                                <p class="text-sm text-gray-600 mt-2">
                                    休憩終了: {{ $attendance->break_end->format('H:i') }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- 今日の勤怠状況 -->
                    @if($attendance)
                        <div class="card">
                            <div class="card-header">
                                <h3 class="text-lg font-semibold">今日の勤怠状況</h3>
                            </div>
                            <div class="card-body">
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div>
                                        <label class="form-label">出勤時刻</label>
                                        <div class="attendance-time">
                                            {{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '--:--' }}
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label">退勤時刻</label>
                                        <div class="attendance-time">
                                            {{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '--:--' }}
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label">休憩時間</label>
                                        <div class="attendance-time">
                                            {{ $attendance->formatted_break_time }}
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label">勤務時間</label>
                                        <div class="attendance-time">
                                            {{ $attendance->formatted_work_time }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- ナビゲーション -->
                    <div class="mt-8 text-center">
                        <a href="{{ route('attendance.list') }}" class="btn btn-secondary mr-4">
                            勤怠一覧を見る
                        </a>
                        <a href="{{ route('stamp_correction_request.list') }}" class="btn btn-secondary">
                            修正申請一覧
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // 現在時刻を1秒ごとに更新
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('ja-JP', { 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit' 
            });
            document.getElementById('currentTime').textContent = timeString;
        }

        setInterval(updateTime, 1000);
    </script>
</x-app-layout>
