<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            勤怠詳細（管理者） - {{ $attendance->user->name }}さんの{{ $attendance->work_date->format('Y年m月d日 (D)') }}
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

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <!-- ナビゲーション -->
                    <div class="mb-6">
                        <a href="{{ route('admin.attendance.list') }}" class="btn btn-secondary">
                            ← 勤怠一覧
                        </a>
                    </div>

                    <!-- 基本情報 -->
                    <div class="card mb-6">
                        <div class="card-header">
                            <h3 class="text-lg font-semibold">基本情報</h3>
                        </div>
                        <div class="card-body">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">氏名</label>
                                    <p class="text-gray-900">{{ $attendance->user->name }}</p>
                                </div>
                                <div>
                                    <label class="form-label">メールアドレス</label>
                                    <p class="text-gray-900">{{ $attendance->user->email }}</p>
                                </div>
                                <div>
                                    <label class="form-label">日付</label>
                                    <p class="text-gray-900">{{ $attendance->work_date->format('Y年m月d日 (D)') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 打刻情報 -->
                    <div class="card mb-6">
                        <div class="card-header">
                            <h3 class="text-lg font-semibold">打刻情報</h3>
                        </div>
                        <div class="card-body">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- 出勤・退勤 -->
                                <div>
                                    <h4 class="font-semibold text-gray-700 mb-3">出勤・退勤</h4>
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">出勤時刻:</span>
                                            <span class="attendance-time">
                                                {{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '未打刻' }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">退勤時刻:</span>
                                            <span class="attendance-time">
                                                {{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '未打刻' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- 休憩 -->
                                <div>
                                    <h4 class="font-semibold text-gray-700 mb-3">休憩</h4>
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">休憩開始:</span>
                                            <span class="attendance-time">
                                                {{ $attendance->break_start ? $attendance->break_start->format('H:i') : '未取得' }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">休憩終了:</span>
                                            <span class="attendance-time">
                                                {{ $attendance->break_end ? $attendance->break_end->format('H:i') : '未取得' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 勤務時間 -->
                    <div class="card mb-6">
                        <div class="card-header">
                            <h3 class="text-lg font-semibold">勤務時間</h3>
                        </div>
                        <div class="card-body">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">総勤務時間:</span>
                                    <span class="attendance-time text-blue-600">
                                        {{ $attendance->formatted_work_time }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">休憩時間:</span>
                                    <span class="attendance-time text-yellow-600">
                                        {{ $attendance->formatted_break_time }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 備考 -->
                    @if($attendance->notes)
                    <div class="card mb-6">
                        <div class="card-header">
                            <h3 class="text-lg font-semibold">備考</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-gray-900">{{ $attendance->notes }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- 編集操作 -->
                    <div class="card mb-6">
                        <div class="card-header">
                            <h3 class="text-lg font-semibold">勤怠情報編集</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-gray-600 mb-4">
                                管理者権限で勤怠情報を直接編集できます。
                            </p>
                            <a href="{{ route('admin.attendance.edit', $attendance) }}" 
                               class="btn btn-primary">
                                勤怠情報を編集
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
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">申請日時</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">修正項目</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ステータス</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">理由</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($attendance->stampCorrectionRequests as $request)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $request->created_at->format('Y/m/d H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $request->request_type_label }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="attendance-status {{ $request->status_class }}">
                                                    {{ $request->status_label }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                {{ $request->reason }}
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

