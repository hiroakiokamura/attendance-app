<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $user->name }}さんの勤怠一覧 - {{ Carbon\Carbon::parse($currentMonth.'-01')->format('Y年m月') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <!-- ナビゲーションとフィルター -->
                    <div class="flex justify-between items-center mb-6">
                        <a href="{{ route('admin.staff.list') }}" class="btn btn-secondary">
                            ← スタッフ一覧
                        </a>
                        
                        <!-- 月フィルター -->
                        <div class="flex items-center space-x-4">
                            <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $prevMonth]) }}" 
                               class="btn btn-outline">
                                ← 前月
                            </a>
                            <span class="text-lg font-semibold">
                                {{ Carbon\Carbon::parse($currentMonth.'-01')->format('Y年m月') }}
                            </span>
                            <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $nextMonth]) }}" 
                               class="btn btn-outline">
                                翌月 →
                            </a>
                        </div>
                    </div>

                    <!-- スタッフ情報 -->
                    <div class="card mb-6">
                        <div class="card-header">
                            <h3 class="text-lg font-semibold">スタッフ情報</h3>
                        </div>
                        <div class="card-body">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <span class="text-gray-600">氏名:</span>
                                    <span class="ml-2 font-medium">{{ $user->name }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">メールアドレス:</span>
                                    <span class="ml-2">{{ $user->email }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 勤怠一覧テーブル -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="text-lg font-semibold">勤怠一覧</h3>
                        </div>
                        <div class="card-body">
                            @if($attendances->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">日付</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">出勤時刻</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">退勤時刻</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">休憩時間</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">勤務時間</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($attendances as $attendance)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ $attendance->work_date->format('m/d (D)') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '-' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '-' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $attendance->formatted_break_time }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $attendance->formatted_work_time }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ route('admin.attendance.detail', $attendance->id) }}"
                                                       class="text-indigo-600 hover:text-indigo-900">詳細</a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <!-- ページネーション -->
                                <div class="mt-6">
                                    {{ $attendances->appends(request()->query())->links() }}
                                </div>
                            @else
                                <div class="text-center py-12">
                                    <p class="text-gray-500">該当する勤怠記録がありません。</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


