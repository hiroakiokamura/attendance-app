<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>スタッフ一覧 - COACHTECH</title>
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
                       class="text-white hover:text-gray-300 transition-colors font-semibold">
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
                スタッフ一覧
            </h1>
        </div>

        <!-- スタッフ一覧テーブル -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-medium text-gray-700">
                            名前
                        </th>
                        <th class="px-6 py-4 text-left text-sm font-medium text-gray-700">
                            メールアドレス
                        </th>
                        <th class="px-6 py-4 text-left text-sm font-medium text-gray-700">
                            月次勤怠
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $user->name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $user->email }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <a href="{{ route('admin.attendance.staff', $user->id) }}" 
                                   class="text-blue-600 hover:text-blue-800 text-sm font-medium transition-colors">
                                    詳細
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500">
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
        @if($users->hasPages())
            <div class="mt-6">
                {{ $users->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</body>
</html>