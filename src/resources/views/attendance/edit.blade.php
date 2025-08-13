<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            勤怠編集 - {{ $attendance->work_date->format('Y年m月d日 (D)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
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
                    <!-- ナビゲーション -->
                    <div class="mb-6">
                        <a href="{{ route('attendance.detail', $attendance->id) }}" class="btn btn-secondary">
                            ← 勤怠詳細に戻る
                        </a>
                    </div>

                    <!-- 編集フォーム -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="text-lg font-semibold">勤怠情報編集</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('attendance.update', $attendance->id) }}">
                                @csrf
                                @method('PUT')

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- 出勤時刻 -->
                                    <div>
                                        <label class="form-label required">出勤時刻</label>
                                        <input type="time" name="clock_in" class="form-input" 
                                               value="{{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '' }}" required>
                                        <x-input-error :messages="$errors->get('clock_in')" class="mt-2" />
                                    </div>

                                    <!-- 退勤時刻 -->
                                    <div>
                                        <label class="form-label required">退勤時刻</label>
                                        <input type="time" name="clock_out" class="form-input" 
                                               value="{{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '' }}" required>
                                        <x-input-error :messages="$errors->get('clock_out')" class="mt-2" />
                                    </div>

                                    <!-- 休憩開始時刻 -->
                                    <div>
                                        <label class="form-label">休憩開始時刻</label>
                                        <input type="time" name="break_start" class="form-input" 
                                               value="{{ $attendance->break_start ? $attendance->break_start->format('H:i') : '' }}">
                                        <x-input-error :messages="$errors->get('break_start')" class="mt-2" />
                                    </div>

                                    <!-- 休憩終了時刻 -->
                                    <div>
                                        <label class="form-label">休憩終了時刻</label>
                                        <input type="time" name="break_end" class="form-input" 
                                               value="{{ $attendance->break_end ? $attendance->break_end->format('H:i') : '' }}">
                                        <x-input-error :messages="$errors->get('break_end')" class="mt-2" />
                                    </div>
                                </div>

                                <!-- 備考 -->
                                <div class="mt-6">
                                    <label class="form-label required">備考</label>
                                    <textarea name="notes" class="form-input" rows="3" required placeholder="修正理由を記入してください">{{ old('notes', $attendance->notes) }}</textarea>
                                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                                </div>

                                <!-- 送信ボタン -->
                                <div class="flex justify-end space-x-4 mt-6">
                                    <a href="{{ route('attendance.detail', $attendance->id) }}" class="btn btn-secondary">
                                        キャンセル
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        保存
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
