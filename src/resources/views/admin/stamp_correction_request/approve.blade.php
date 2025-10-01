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
                
                <!-- ナビゲーション -->
                <nav class="flex items-center space-x-8">
                    <a href="{{ route('admin.attendance.list') }}" 
                       class="text-white hover:text-gray-300 transition-colors">
                        勤怠一覧
                    </a>
                    <a href="{{ route('admin.staff.list') }}" 
                       class="text-white hover:text-gray-300 transition-colors">
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
                勤怠詳細
            </h1>
        </div>

        <!-- アラートメッセージ -->
        @if (session('success'))
            <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        <!-- 勤怠詳細 -->
        <div class="bg-white rounded-lg shadow-sm p-8 mb-8">
            <!-- 名前 -->
            <div class="flex items-center py-6 border-b border-gray-200">
                <div class="w-24 text-base font-medium text-gray-700">
                    名前
                </div>
                <div class="flex-1 ml-16 text-base text-gray-900">
                    {{ $request->user->name }}
                </div>
            </div>

            <!-- 日付 -->
            <div class="flex items-center py-6 border-b border-gray-200">
                <div class="w-24 text-base font-medium text-gray-700">
                    日付
                </div>
                <div class="flex-1 ml-16 text-base text-gray-900">
                    <span class="mr-8">{{ $request->attendance->work_date->format('Y年') }}</span>
                    <span>{{ $request->attendance->work_date->format('n月j日') }}</span>
                </div>
            </div>

            <!-- 出勤・退勤 -->
            <div class="flex items-center py-6 border-b border-gray-200">
                <div class="w-24 text-base font-medium text-gray-700">
                    出勤・退勤
                </div>
                <div class="flex-1 ml-16 text-base text-gray-900">
                    <span class="mr-8">{{ $attendanceData->clock_in ? $attendanceData->clock_in->format('H:i') : '09:00' }}</span>
                    <span class="mr-8">～</span>
                    <span>{{ $attendanceData->clock_out ? $attendanceData->clock_out->format('H:i') : '18:00' }}</span>
                </div>
            </div>

            <!-- 休憩 -->
            <div class="flex items-center py-6 border-b border-gray-200">
                <div class="w-24 text-base font-medium text-gray-700">
                    休憩
                </div>
                <div class="flex-1 ml-16 text-base text-gray-900">
                    @if($attendanceData->breakTimes && $attendanceData->breakTimes->count() > 0)
                        @php $firstBreak = $attendanceData->breakTimes->first(); @endphp
                        <span class="mr-8">{{ $firstBreak->start_time ? $firstBreak->start_time->format('H:i') : '12:00' }}</span>
                        <span class="mr-8">～</span>
                        <span>{{ $firstBreak->end_time ? $firstBreak->end_time->format('H:i') : '13:00' }}</span>
                    @elseif($attendanceData->break_start && $attendanceData->break_end)
                        <span class="mr-8">{{ $attendanceData->break_start->format('H:i') }}</span>
                        <span class="mr-8">～</span>
                        <span>{{ $attendanceData->break_end->format('H:i') }}</span>
                    @else
                        <span class="mr-8">12:00</span>
                        <span class="mr-8">～</span>
                        <span>13:00</span>
                    @endif
                </div>
            </div>

            <!-- 休憩2 -->
            <div class="flex items-center py-6 border-b border-gray-200">
                <div class="w-24 text-base font-medium text-gray-700">
                    休憩2
                </div>
                <div class="flex-1 ml-16 text-base text-gray-900">
                    @if($attendanceData->breakTimes && $attendanceData->breakTimes->count() > 1)
                        @php $secondBreak = $attendanceData->breakTimes->skip(1)->first(); @endphp
                        <span class="mr-8">{{ $secondBreak->start_time ? $secondBreak->start_time->format('H:i') : '' }}</span>
                        @if($secondBreak->start_time && $secondBreak->end_time)
                            <span class="mr-8">～</span>
                            <span>{{ $secondBreak->end_time->format('H:i') }}</span>
                        @endif
                    @else
                        <!-- 空欄 -->
                    @endif
                </div>
            </div>

            <!-- 備考 -->
            <div class="flex items-center py-6">
                <div class="w-24 text-base font-medium text-gray-700">
                    備考
                </div>
                <div class="flex-1 ml-16 text-base text-gray-900">
                    {{ $request->reason ?? '電車遅延のため' }}
                </div>
            </div>
        </div>

        <!-- 承認ボタン -->
        @if($request->status === 'pending')
            <div class="flex justify-end">
                <button id="approveBtn" 
                        class="bg-black hover:bg-gray-800 text-white px-8 py-3 rounded text-base font-medium transition-colors"
                        onclick="approveRequest({{ $request->id }})">
                    承認
                </button>
            </div>
        @else
            <!-- 処理済みの場合 -->
            <div class="flex justify-end">
                <div class="px-8 py-3 rounded text-base font-medium
                    {{ $request->status === 'approved' ? 'bg-green-600 text-white' : 'bg-red-600 text-white' }}">
                    {{ $request->status === 'approved' ? '承認済み' : '却下済み' }}
                </div>
            </div>
        @endif
    </div>

    <script>
        function approveRequest(requestId) {
            if (!confirm('この申請を承認しますか？勤怠記録が更新されます。')) {
                return;
            }

            const btn = document.getElementById('approveBtn');
            const originalText = btn.textContent;
            
            // ボタンを無効化
            btn.disabled = true;
            btn.textContent = '処理中...';
            btn.classList.remove('hover:bg-gray-800');
            btn.classList.add('opacity-50', 'cursor-not-allowed');

            // AJAX リクエスト
            fetch(`/admin/stamp_correction_request/approve/${requestId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    action: 'approve'
                })
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                
                if (data.success) {
                    // 承認成功時
                    btn.textContent = '承認済み';
                    btn.classList.remove('bg-black', 'opacity-50');
                    btn.classList.add('bg-green-600', 'text-white');
                    
                    // 成功メッセージを表示
                    if (data.message) {
                        // 既存のアラートメッセージを削除
                        const existingAlert = document.querySelector('.success-alert');
                        if (existingAlert) {
                            existingAlert.remove();
                        }
                        
                        // 新しい成功メッセージを作成
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'success-alert mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded';
                        alertDiv.textContent = data.message;
                        
                        // より安全な方法でページ先頭に挿入
                        try {
                            const container = document.querySelector('.container');
                            if (container) {
                                container.insertAdjacentElement('afterbegin', alertDiv);
                            } else {
                                // containerが見つからない場合はbodyに追加
                                document.body.insertAdjacentElement('afterbegin', alertDiv);
                            }
                        } catch (e) {
                            console.error('Failed to insert success message:', e);
                            // フォールバック：シンプルなalertを使用
                            alert(data.message);
                        }
                        
                        // 3秒後にメッセージを非表示
                        setTimeout(() => {
                            if (alertDiv && alertDiv.parentNode) {
                                alertDiv.style.transition = 'opacity 0.3s';
                                alertDiv.style.opacity = '0';
                                setTimeout(() => {
                                    if (alertDiv && alertDiv.parentNode) {
                                        alertDiv.remove();
                                    }
                                }, 300);
                            }
                        }, 3000);
                    }
                } else {
                    // エラー時
                    btn.disabled = false;
                    btn.textContent = originalText;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                    btn.classList.add('hover:bg-gray-800');
                    alert('承認処理に失敗しました: ' + (data.message || '不明なエラー'));
                }
            })
            .catch(error => {
                // ネットワークエラー等
                console.error('Fetch error details:', error);
                btn.disabled = false;
                btn.textContent = originalText;
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
                btn.classList.add('hover:bg-gray-800');
                alert('承認処理に失敗しました。詳細: ' + error.message);
            });
        }
    </script>
</body>
</html>