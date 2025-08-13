<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                スタッフ一覧（管理者）
            </h2>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="btn btn-secondary">ログアウト</button>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <!-- ナビゲーション -->
                    <div class="mb-6 flex flex-wrap gap-4">
                        <a href="{{ route('admin.attendance.list') }}" class="btn btn-secondary">
                            勤怠一覧
                        </a>
                        <a href="{{ route('admin.stamp_correction_request.list') }}" class="btn btn-secondary">
                            修正申請一覧
                        </a>
                    </div>

                    <!-- 検索フォーム -->
                    <div class="card mb-6">
                        <div class="card-header">
                            <h3 class="text-lg font-semibold">スタッフ検索</h3>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="flex gap-4">
                                <div class="flex-1">
                                    <input type="text" name="search" class="form-input" 
                                           placeholder="名前またはメールアドレスで検索" 
                                           value="{{ request('search') }}">
                                </div>
                                <button type="submit" class="btn btn-primary">検索</button>
                                @if(request('search'))
                                    <a href="{{ route('admin.staff.list') }}" class="btn btn-secondary">
                                        クリア
                                    </a>
                                @endif
                            </form>
                        </div>
                    </div>

                    <!-- スタッフ一覧テーブル -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        名前
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        メールアドレス
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        登録日
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        勤怠記録数
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        操作
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($users as $user)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $user->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $user->email }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $user->created_at->format('Y/m/d') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $user->attendances_count }}件
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <a href="{{ route('admin.attendance.staff', $user->id) }}" 
                                               class="text-blue-600 hover:text-blue-900 mr-4">
                                                勤怠詳細
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                            @if(request('search'))
                                                検索条件に一致するスタッフが見つかりません
                                            @else
                                                スタッフが登録されていません
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- ページネーション -->
                    <div class="mt-6">
                        {{ $users->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
