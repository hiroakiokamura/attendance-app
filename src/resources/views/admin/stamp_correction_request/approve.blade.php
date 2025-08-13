<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                修正申請承認
            </h2>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="btn btn-secondary">ログアウト</button>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <!-- ナビゲーション -->
                    <div class="mb-6">
                        <a href="{{ route('admin.stamp_correction_request.list') }}" class="btn btn-secondary">
                            ← 申請一覧に戻る
                        </a>
                    </div>

                    <!-- 申請者情報 -->
                    <div class="card mb-6">
                        <div class="card-header">
                            <h3 class="text-lg font-semibold">申請者情報</h3>
                        </div>
                        <div class="card-body">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="form-label">申請者</label>
                                    <div class="font-semibold">{{ $request->user->name }}</div>
                                </div>
                                <div>
                                    <label class="form-label">メールアドレス</label>
                                    <div>{{ $request->user->email }}</div>
                                </div>
                                <div>
                                    <label class="form-label">申請日時</label>
                                    <div>{{ $request->created_at->format('Y年m月d日 H:i') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 対象勤怠情報 -->
                    <div class="card mb-6">
                        <div class="card-header">
                            <h3 class="text-lg font-semibold">対象勤怠記録</h3>
                        </div>
                        <div class="card-body">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="form-label">対象日</label>
                                    <div class="font-semibold">
                                        {{ $request->attendance->work_date->format('Y年m月d日 (D)') }}
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label">出勤時刻</label>
                                    <div>
                                        {{ $request->attendance->clock_in ? $request->attendance->clock_in->format('H:i') : '--:--' }}
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label">退勤時刻</label>
                                    <div>
                                        {{ $request->attendance->clock_out ? $request->attendance->clock_out->format('H:i') : '--:--' }}
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label">勤務時間</label>
                                    <div>
                                        {{ $request->attendance->formatted_work_time }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 修正申請詳細 -->
                    <div class="card mb-6">
                        <div class="card-header">
                            <h3 class="text-lg font-semibold">修正申請内容</h3>
                        </div>
                        <div class="card-body">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="form-label">修正項目</label>
                                    <div class="text-lg font-semibold text-blue-600">
                                        {{ $request->request_type_label }}
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label">現在の時刻</label>
                                    <div class="text-lg">
                                        {{ $request->original_time ? $request->original_time->format('H:i') : '--:--' }}
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label">修正希望時刻</label>
                                    <div class="text-lg font-semibold text-red-600">
                                        {{ $request->requested_time->format('H:i') }}
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6">
                                <label class="form-label">修正理由</label>
                                <div class="p-4 bg-gray-50 rounded border">
                                    {{ $request->reason }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 承認フォーム -->
                    @if($request->status === 'pending')
                        <div class="card">
                            <div class="card-header">
                                <h3 class="text-lg font-semibold">承認・却下</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('admin.stamp_correction_request.process', $request->id) }}">
                                    @csrf
                                    
                                    <!-- 管理者コメント -->
                                    <div class="mb-6">
                                        <label class="form-label">管理者コメント（任意）</label>
                                        <textarea name="admin_comment" class="form-input" rows="3" 
                                                  placeholder="承認・却下の理由やコメントがあれば記入してください">{{ old('admin_comment') }}</textarea>
                                        <x-input-error :messages="$errors->get('admin_comment')" class="mt-2" />
                                    </div>

                                    <!-- アクションボタン -->
                                    <div class="flex justify-end space-x-4">
                                        <button type="submit" name="action" value="reject" 
                                                class="btn btn-danger"
                                                onclick="return confirm('この申請を却下しますか？')">
                                            却下
                                        </button>
                                        <button type="submit" name="action" value="approve" 
                                                class="btn btn-success"
                                                onclick="return confirm('この申請を承認しますか？勤怠記録が更新されます。')">
                                            承認
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @else
                        <!-- 処理済み申請の表示 -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="text-lg font-semibold">処理結果</h3>
                            </div>
                            <div class="card-body">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                    <div>
                                        <label class="form-label">ステータス</label>
                                        <div>
                                            <span class="attendance-status {{ $request->status_class }}">
                                                {{ $request->status_label }}
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label">処理日時</label>
                                        <div>{{ $request->approved_at->format('Y年m月d日 H:i') }}</div>
                                    </div>
                                    <div>
                                        <label class="form-label">処理者</label>
                                        <div>{{ $request->approver->name }}</div>
                                    </div>
                                </div>

                                @if($request->admin_comment)
                                    <div>
                                        <label class="form-label">管理者コメント</label>
                                        <div class="p-4 bg-gray-50 rounded border">
                                            {{ $request->admin_comment }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
