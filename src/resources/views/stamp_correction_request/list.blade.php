<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>申請一覧 - COACHTECH</title>
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
                <nav class="flex items-center space-x-6">
                    <a href="{{ route('attendance.index') }}" class="text-white hover:text-gray-300 transition-colors">
                        勤怠
                    </a>
                    <a href="{{ route('attendance.list') }}" class="text-white hover:text-gray-300 transition-colors">
                        勤怠一覧
                    </a>
                    <a href="{{ route('stamp_correction_request.list') }}" class="text-white hover:text-gray-300 transition-colors">
                        申請
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
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
    <div class="bg-gray-100 min-h-screen" style="min-height: calc(100vh - 80px);">
        <div class="container mx-auto px-4 py-8">
            <!-- タイトル -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-800">申請一覧</h1>
            </div>

            <!-- アラートメッセージ -->
            @if (session('success'))
                <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded max-w-4xl mx-auto">
                    {{ session('success') }}
                </div>
            @endif

            <!-- タブナビゲーション -->
            <div class="mb-6 max-w-4xl mx-auto">
                <div class="flex border-b border-gray-300">
                    <a href="{{ route('stamp_correction_request.list', ['status' => 'pending']) }}" 
                       class="px-6 py-3 {{ ($status ?? 'pending') === 'pending' ? 'text-gray-800 border-b-2 border-gray-800 font-medium' : 'text-gray-600 hover:text-gray-800 transition-colors' }}">
                        承認待ち
                    </a>
                    <a href="{{ route('stamp_correction_request.list', ['status' => 'approved']) }}" 
                       class="px-6 py-3 {{ ($status ?? 'pending') === 'approved' ? 'text-gray-800 border-b-2 border-gray-800 font-medium' : 'text-gray-600 hover:text-gray-800 transition-colors' }}">
                        承認済み
                    </a>
                </div>
            </div>

            <!-- 申請一覧テーブル -->
            <div class="max-w-4xl mx-auto">
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-center text-sm font-medium text-gray-700">
                                    状態
                                </th>
                                <th class="px-6 py-3 text-center text-sm font-medium text-gray-700">
                                    名前
                                </th>
                                <th class="px-6 py-3 text-center text-sm font-medium text-gray-700">
                                    対象日時
                                </th>
                                <th class="px-6 py-3 text-center text-sm font-medium text-gray-700">
                                    申請理由
                                </th>
                                <th class="px-6 py-3 text-center text-sm font-medium text-gray-700">
                                    申請日時
                                </th>
                                <th class="px-6 py-3 text-center text-sm font-medium text-gray-700">
                                    詳細
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($requests as $request)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-center text-sm text-gray-900">
                                        @if($request->status === 'pending')
                                            承認待ち
                                        @elseif($request->status === 'approved')
                                            承認済み
                                        @else
                                            却下
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm text-gray-900">
                                        {{ $request->user->name }}
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm text-gray-900">
                                        {{ $request->attendance->work_date->format('Y/m/d') }}
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm text-gray-900">
                                        {{ Str::limit($request->reason, 20) }}
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm text-gray-900">
                                        {{ $request->created_at->format('Y/m/d') }}
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm text-gray-900">
                                        <a href="{{ route('attendance.detail', ['id' => $request->attendance_id, 'request_status' => $request->status]) }}" class="text-blue-600 hover:text-blue-800 underline">
                                            詳細
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                        @if(($status ?? 'pending') === 'pending')
                                            承認待ちの申請がありません
                                        @elseif(($status ?? 'pending') === 'approved')
                                            承認済みの申請がありません
                                        @else
                                            修正申請がありません
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ページネーション -->
            @if($requests->hasPages())
                <div class="mt-8 flex justify-center">
                    {{ $requests->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</body>
</html>
