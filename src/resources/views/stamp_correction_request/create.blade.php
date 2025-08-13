<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            修正申請作成
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <!-- ナビゲーション -->
                    <div class="mb-6">
                        <a href="{{ route('attendance.detail', $attendance->id) }}" class="btn btn-secondary">
                            ← 勤怠詳細に戻る
                        </a>
                    </div>

                    <!-- 対象勤怠情報 -->
                    <div class="card mb-6">
                        <div class="card-header">
                            <h3 class="text-lg font-semibold">修正対象の勤怠記録</h3>
                        </div>
                        <div class="card-body">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="form-label">対象日</label>
                                    <div class="font-semibold">
                                        {{ $attendance->work_date->format('Y年m月d日 (D)') }}
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label">出勤時刻</label>
                                    <div class="font-semibold">
                                        {{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '--:--' }}
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label">退勤時刻</label>
                                    <div class="font-semibold">
                                        {{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '--:--' }}
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label">休憩時間</label>
                                    <div class="font-semibold">
                                        {{ $attendance->formatted_break_time }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 修正申請フォーム -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="text-lg font-semibold">修正申請フォーム</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('stamp_correction_request.store') }}">
                                @csrf
                                <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">

                                <!-- 修正項目選択 -->
                                <div class="mb-4">
                                    <label class="form-label required">修正項目</label>
                                    <select name="request_type" class="form-input" required>
                                        <option value="">選択してください</option>
                                        @if($attendance->clock_in)
                                            <option value="clock_in" {{ old('request_type') === 'clock_in' ? 'selected' : '' }}>
                                                出勤時刻
                                            </option>
                                        @endif
                                        @if($attendance->clock_out)
                                            <option value="clock_out" {{ old('request_type') === 'clock_out' ? 'selected' : '' }}>
                                                退勤時刻
                                            </option>
                                        @endif
                                        @if($attendance->break_start)
                                            <option value="break_start" {{ old('request_type') === 'break_start' ? 'selected' : '' }}>
                                                休憩開始時刻
                                            </option>
                                        @endif
                                        @if($attendance->break_end)
                                            <option value="break_end" {{ old('request_type') === 'break_end' ? 'selected' : '' }}>
                                                休憩終了時刻
                                            </option>
                                        @endif
                                    </select>
                                    <x-input-error :messages="$errors->get('request_type')" class="mt-2" />
                                </div>

                                <!-- 修正希望時刻 -->
                                <div class="mb-4">
                                    <label class="form-label required">修正希望時刻</label>
                                    <input type="time" name="requested_time" class="form-input" value="{{ old('requested_time') }}" required>
                                    <x-input-error :messages="$errors->get('requested_time')" class="mt-2" />
                                </div>

                                <!-- 修正理由 -->
                                <div class="mb-6">
                                    <label class="form-label required">修正理由</label>
                                    <textarea name="reason" class="form-input" rows="4" placeholder="修正が必要な理由を詳しく記入してください" required>{{ old('reason') }}</textarea>
                                    <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                                </div>

                                <!-- 送信ボタン -->
                                <div class="flex justify-end space-x-4">
                                    <a href="{{ route('attendance.detail', $attendance->id) }}" class="btn btn-secondary">
                                        キャンセル
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        申請を送信
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .required::after {
            content: ' *';
            color: #ef4444;
        }
    </style>
</x-app-layout>
