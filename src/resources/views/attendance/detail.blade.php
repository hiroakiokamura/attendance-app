<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            勤怠詳細 - {{ $attendance->work_date->format('Y年m月d日 (D)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <!-- ナビゲーション -->
                    <div class="mb-6">
                        <a href="{{ route('attendance.list') }}" class="btn btn-secondary">
                            ← 勤怠一覧に戻る
                        </a>
                    </div>

                    <!-- 勤怠詳細情報 -->
                    <div class="card mb-6">
                        <div class="card-header">
                            <h3 class="text-lg font-semibold">勤怠詳細</h3>
                        </div>
                        <div class="card-body">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="form-label">勤務日</label>
                                    <div class="text-xl font-semibold text-gray-800">
                                        {{ $attendance->work_date->format('Y年m月d日 (D)') }}
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label">ステータス</label>
                                    <div class="text-xl">
                                        @if($attendance->clock_out)
                                            <span class="status-finished attendance-status">勤務完了</span>
                                        @elseif($attendance->break_start && !$attendance->break_end)
                                            <span class="status-break attendance-status">休憩中</span>
                                        @elseif($attendance->clock_in)
                                            <span class="status-working attendance-status">勤務中</span>
                                        @else
                                            <span class="bg-gray-100 text-gray-800 attendance-status">未出勤</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-6">
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

                            @if($attendance->notes)
                                <div class="mt-6">
                                    <label class="form-label">備考</label>
                                    <div class="p-3 bg-gray-50 rounded">
                                        {{ $attendance->notes }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 修正申請ボタン -->
                    <div class="card mb-6">
                        <div class="card-header">
                            <h3 class="text-lg font-semibold">打刻修正申請</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-gray-600 mb-4">
                                打刻時刻に誤りがある場合は、修正申請を行うことができます。
                            </p>
                            <a href="{{ route('stamp_correction_request.create', $attendance) }}" 
                               class="btn btn-primary">
                                修正申請を作成
                            </a>
                        </div>
                    </div>

                    <!-- 修正申請履歴 -->
                    @if($attendance->stampCorrectionRequests->count() > 0)
                        <div class="card">
                            <div class="card-header">
                                <h3 class="text-lg font-semibold">修正申請履歴</h3>
                            </div>
                            <div class="card-body">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                    申請日時
                                                </th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                    修正項目
                                                </th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                    申請時刻
                                                </th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                    ステータス
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($attendance->stampCorrectionRequests as $request)
                                                <tr>
                                                    <td class="px-4 py-2 text-sm text-gray-900">
                                                        {{ $request->created_at->format('m/d H:i') }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-gray-900">
                                                        {{ $request->request_type_label }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-gray-900">
                                                        {{ $request->requested_time->format('H:i') }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm">
                                                        <span class="attendance-status {{ $request->status_class }}">
                                                            {{ $request->status_label }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
