<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StampCorrectionRequestController extends Controller
{
    /**
     * PG06: 申請一覧画面（一般ユーザー）
     */
    public function list(Request $request)
    {
        $user = Auth::user();
        
        $query = StampCorrectionRequest::with(['attendance'])
            ->where('user_id', $user->id);

        // ステータスフィルター（デフォルトは承認待ち）
        $status = $request->status ?? 'pending';
        if ($status) {
            $query->where('status', $status);
        }
        
        $requests = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('stamp_correction_request.list', compact('requests', 'status'));
    }

    /**
     * 修正申請作成画面
     */
    public function create(Attendance $attendance)
    {
        // 自分の勤怠記録かチェック
        if ($attendance->user_id !== Auth::id()) {
            abort(403);
        }

        return view('stamp_correction_request.create', compact('attendance'));
    }

    /**
     * 修正申請保存
     */
    public function store(Request $request)
    {
        $request->validate([
            'attendance_id' => 'required|exists:attendances,id',
            'request_type' => 'required|in:clock_in,clock_out,break_start,break_end',
            'requested_time' => 'required|date_format:H:i',
            'reason' => 'required|string|max:1000',
        ]);

        $attendance = Attendance::findOrFail($request->attendance_id);
        
        // 自分の勤怠記録かチェック
        if ($attendance->user_id !== Auth::id()) {
            abort(403);
        }

        // 元の時刻を取得
        $originalTime = match($request->request_type) {
            'clock_in' => $attendance->clock_in,
            'clock_out' => $attendance->clock_out,
            'break_start' => $attendance->break_start,
            'break_end' => $attendance->break_end,
        };

        // 申請時刻を作成
        $requestedDateTime = Carbon::parse($attendance->work_date->format('Y-m-d') . ' ' . $request->requested_time);

        StampCorrectionRequest::create([
            'user_id' => Auth::id(),
            'attendance_id' => $attendance->id,
            'request_type' => $request->request_type,
            'original_time' => $originalTime,
            'requested_time' => $requestedDateTime,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()->route('stamp_correction_request.list')
            ->with('success', '修正申請を送信しました。');
    }

    /**
     * 詳細画面からの修正申請保存
     */
    public function storeFromDetail(Request $request)
    {
        $request->validate([
            'attendance_id' => 'required|exists:attendances,id',
            'clock_in' => 'nullable|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i',
            'break_times' => 'nullable|array',
            'break_times.*.start_time' => 'nullable|date_format:H:i',
            'break_times.*.end_time' => 'nullable|date_format:H:i',
            'notes' => 'required|string|max:1000',
        ]);

        // カスタムバリデーション
        $clockIn = $request->clock_in;
        $clockOut = $request->clock_out;
        $breakTimes = $request->break_times ?? [];

        // 出勤時間が退勤時間より後の場合（同時刻は許可）
        if ($clockIn && $clockOut && $clockIn > $clockOut) {
            return back()->withErrors(['clock_in' => '出勤時間は退勤時間より前の時刻を入力してください']);
        }

        // 休憩時間のバリデーション
        foreach ($breakTimes as $index => $breakTime) {
            $breakStart = $breakTime['start_time'] ?? null;
            $breakEnd = $breakTime['end_time'] ?? null;
            
            if ($breakStart && $breakEnd) {
                // 休憩開始時間が休憩終了時間より後の場合（同時刻は許可）
                if ($breakStart > $breakEnd) {
                    return back()->withErrors(["break_times.{$index}.start_time" => '休憩開始時間は休憩終了時間より前の時刻を入力してください']);
                }
                
                // 休憩開始時間が出勤時間より前の場合
                if ($clockIn && $breakStart < $clockIn) {
                    return back()->withErrors(["break_times.{$index}.start_time" => '休憩開始時間は出勤時間以降の時刻を入力してください']);
                }
                
                // 休憩終了時間が退勤時間より後の場合
                if ($clockOut && $breakEnd > $clockOut) {
                    return back()->withErrors(["break_times.{$index}.end_time" => '休憩終了時間は退勤時間以前の時刻を入力してください']);
                }
            }
        }

        // 休憩時間の重複チェック
        for ($i = 0; $i < count($breakTimes); $i++) {
            for ($j = $i + 1; $j < count($breakTimes); $j++) {
                $break1Start = $breakTimes[$i]['start_time'] ?? null;
                $break1End = $breakTimes[$i]['end_time'] ?? null;
                $break2Start = $breakTimes[$j]['start_time'] ?? null;
                $break2End = $breakTimes[$j]['end_time'] ?? null;
                
                if ($break1Start && $break1End && $break2Start && $break2End) {
                    // 休憩時間が重複している場合
                    if (($break1Start < $break2End && $break1End > $break2Start)) {
                        return back()->withErrors(["break_times.{$j}.start_time" => '休憩時間が重複しています']);
                    }
                }
            }
        }

        $attendance = Attendance::findOrFail($request->attendance_id);
        
        // 自分の勤怠記録かチェック
        if ($attendance->user_id !== Auth::id()) {
            abort(403);
        }

        $changes = [];
        $reason = $request->notes;

        // 出勤・退勤時刻の変更をチェック
        $basicFields = [
            'clock_in' => '出勤時刻',
            'clock_out' => '退勤時刻'
        ];

        foreach ($basicFields as $field => $label) {
            $originalValue = $attendance->$field;
            $requestedValue = $request->$field;

            if ($requestedValue && $originalValue) {
                $originalTime = $originalValue->format('H:i');
                if ($originalTime !== $requestedValue) {
                    $changes[] = [
                        'field' => $field,
                        'label' => $label,
                        'original' => $originalValue,
                        'requested' => $requestedValue
                    ];
                }
            } elseif ($requestedValue && !$originalValue) {
                $changes[] = [
                    'field' => $field,
                    'label' => $label,
                    'original' => null,
                    'requested' => $requestedValue
                ];
            }
        }

        // 休憩時間の変更をチェック
        $breakTimes = $request->break_times ?? [];
        $hasBreakChanges = !empty(array_filter($breakTimes, function($breakTime) {
            return !empty($breakTime['start_time']) || !empty($breakTime['end_time']);
        }));
        
        // 休憩時間の処理
        if ($hasBreakChanges) {
            // 既存の休憩時間をクリア
            $attendance->breakTimes()->delete();
            
            // 新しい休憩時間を保存
            foreach ($breakTimes as $index => $breakTime) {
                if (!empty($breakTime['start_time']) && !empty($breakTime['end_time'])) {
                    $startDateTime = Carbon::parse($attendance->work_date->format('Y-m-d') . ' ' . $breakTime['start_time']);
                    $endDateTime = Carbon::parse($attendance->work_date->format('Y-m-d') . ' ' . $breakTime['end_time']);
                    
                    \App\Models\BreakTime::create([
                        'attendance_id' => $attendance->id,
                        'start_time' => $startDateTime,
                        'end_time' => $endDateTime,
                        'order' => $index + 1
                    ]);
                }
            }
            
            // 総休憩時間を再計算
            $totalBreakMinutes = $attendance->breakTimes()->get()->sum(function($breakTime) {
                if ($breakTime->start_time && $breakTime->end_time) {
                    return $breakTime->start_time->diffInMinutes($breakTime->end_time);
                }
                return 0;
            });
            
            $attendance->update([
                'total_break_time' => $totalBreakMinutes
            ]);
        }
        
        \Log::info('修正申請処理の結果', [
            'user_id' => Auth::id(),
            'attendance_id' => $attendance->id,
            'changes_count' => count($changes),
            'changes' => $changes,
            'has_break_changes' => $hasBreakChanges,
            'break_times_count' => count($breakTimes)
        ]);

        if (empty($changes) && !$hasBreakChanges) {
            \Log::warning('修正する項目がありません', [
                'user_id' => Auth::id(),
                'attendance_id' => $attendance->id
            ]);
            return back()->withErrors(['修正する項目がありません。']);
        }

        // 各変更に対して修正申請を作成
        foreach ($changes as $change) {
            $requestedDateTime = Carbon::parse($attendance->work_date->format('Y-m-d') . ' ' . $change['requested']);

            $correctionRequest = StampCorrectionRequest::create([
                'user_id' => Auth::id(),
                'attendance_id' => $attendance->id,
                'request_type' => $change['field'],
                'original_time' => $change['original'],
                'requested_time' => $requestedDateTime,
                'reason' => $reason,
                'status' => 'pending',
            ]);

            \Log::info('修正申請を作成しました', [
                'request_id' => $correctionRequest->id,
                'user_id' => Auth::id(),
                'attendance_id' => $attendance->id,
                'request_type' => $change['field'],
                'original_time' => $change['original'],
                'requested_time' => $requestedDateTime,
                'reason' => $reason
            ]);
        }

        // 成功メッセージを設定
        $totalChanges = count($changes);
        $successMessage = '';
        
        if ($totalChanges > 0 && $hasBreakChanges) {
            $successMessage = $totalChanges . '件の修正申請と休憩時間を更新しました。';
        } elseif ($totalChanges > 0) {
            $successMessage = $totalChanges . '件の修正申請を送信しました。';
        } elseif ($hasBreakChanges) {
            $successMessage = '休憩時間を更新しました。';
        }

        return redirect()->route('attendance.detail', $attendance->id)
            ->with('success', $successMessage)
            ->with('pending_request', $totalChanges > 0)
            ->withInput($request->all());
    }

    /**
     * PG12: 申請一覧画面（管理者）
     */
    public function adminList(Request $request)
    {
        $query = StampCorrectionRequest::with(['user', 'attendance']);

        // ステータスフィルター（デフォルトは承認待ち）
        $status = $request->status ?? 'pending';
        if ($status) {
            $query->where('status', $status);
        }

        $requests = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.stamp_correction_request.list', compact('requests'));
    }

    /**
     * PG13: 修正申請承認画面（管理者）
     */
    public function approve($id)
    {
        $request = StampCorrectionRequest::with(['user', 'attendance.breakTimes'])
            ->findOrFail($id);

        // 申請内容を反映した勤怠データを作成
        $attendanceData = $this->getAttendanceWithAppliedChanges($request);

        return view('admin.stamp_correction_request.approve', compact('request', 'attendanceData'));
    }

    /**
     * 申請内容を反映した勤怠データを作成
     */
    private function getAttendanceWithAppliedChanges($correctionRequest)
    {
        $attendance = $correctionRequest->attendance;
        $field = $correctionRequest->request_type;
        $requestedTime = $correctionRequest->requested_time;

        // 現在の勤怠データをコピー
        $data = [
            'clock_in' => $attendance->clock_in,
            'clock_out' => $attendance->clock_out,
            'break_start' => $attendance->break_start,
            'break_end' => $attendance->break_end,
            'breakTimes' => $attendance->breakTimes,
            'notes' => $attendance->notes
        ];

        // 申請内容を反映
        if (in_array($field, ['clock_in', 'clock_out', 'break_start', 'break_end'])) {
            $data[$field] = $requestedTime;
        }

        return (object) $data;
    }

    /**
     * 修正申請承認処理
     */
    public function processApproval(Request $request, $id)
    {
        // AJAX リクエストの場合
        if ($request->ajax() || $request->wantsJson()) {
            return $this->processApprovalAjax($request, $id);
        }

        $validatedData = $request->validate([
            'action' => 'required|in:approve,reject',
            'admin_comment' => 'nullable|string|max:1000',
        ]);

        $correctionRequest = StampCorrectionRequest::with('attendance')->findOrFail($id);

        if ($correctionRequest->status !== 'pending') {
            return back()->with('error', '既に処理済みの申請です。');
        }

        $status = $validatedData['action'] === 'approve' ? 'approved' : 'rejected';

        $correctionRequest->update([
            'status' => $status,
            'admin_comment' => $validatedData['admin_comment'] ?? null,
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        // 承認された場合、勤怠記録を更新
        if ($status === 'approved') {
            $attendance = $correctionRequest->attendance;
            $field = $correctionRequest->request_type;
            
            \Log::info('Starting attendance update after form approval', [
                'request_id' => $correctionRequest->id,
                'attendance_id' => $attendance->id,
                'field' => $field,
                'old_value' => $attendance->{$field},
                'new_value' => $correctionRequest->requested_time
            ]);
            
            try {
                // 出勤・退勤時刻の更新
                if (in_array($field, ['clock_in', 'clock_out'])) {
                    $oldValue = $attendance->{$field};
                    $attendance->update([
                        $field => $correctionRequest->requested_time,
                    ]);
                    
                    // 勤務時間を再計算
                    $attendance->update([
                        'total_work_time' => $attendance->calculateWorkTime(),
                    ]);
                    
                    \Log::info('Form: Clock time updated', [
                        'field' => $field,
                        'old' => $oldValue ? $oldValue->format('H:i:s') : 'null',
                        'new' => $correctionRequest->requested_time->format('H:i:s'),
                        'total_work_time' => $attendance->total_work_time
                    ]);
                }
                
                // 休憩時間の更新（新しいBreakTimeモデル対応）
                elseif (in_array($field, ['break_start', 'break_end'])) {
                    $oldValue = $attendance->{$field};
                    
                    // 古いフィールドも更新（互換性のため）
                    $attendance->update([
                        $field => $correctionRequest->requested_time,
                    ]);
                    
                    // BreakTimeモデルでも更新
                    $this->updateBreakTimeFromRequest($attendance, $field, $correctionRequest->requested_time);
                    
                    // 総休憩時間を再計算
                    $totalBreakMinutes = $attendance->breakTimes()->get()->sum(function($breakTime) {
                        if ($breakTime->start_time && $breakTime->end_time) {
                            return $breakTime->start_time->diffInMinutes($breakTime->end_time);
                        }
                        return 0;
                    });
                    
                    $attendance->update([
                        'total_break_time' => $totalBreakMinutes
                    ]);
                    
                    \Log::info('Form: Break time updated', [
                        'field' => $field,
                        'old' => $oldValue ? $oldValue->format('H:i:s') : 'null',
                        'new' => $correctionRequest->requested_time->format('H:i:s'),
                        'total_break_time' => $totalBreakMinutes
                    ]);
                }
                
                // 備考の更新
                elseif ($field === 'notes') {
                    $attendance->update([
                        'notes' => $correctionRequest->reason
                    ]);
                    
                    \Log::info('Form: Notes updated', [
                        'old' => $attendance->notes,
                        'new' => $correctionRequest->reason
                    ]);
                }
                
                // 最終的な勤怠データを再読み込み
                $attendance->refresh();
                
                \Log::info('Form: Attendance record successfully updated after approval', [
                    'request_id' => $correctionRequest->id,
                    'attendance_id' => $attendance->id,
                    'field' => $field,
                    'final_value' => $attendance->{$field},
                    'clock_in' => $attendance->clock_in ? $attendance->clock_in->format('H:i:s') : 'null',
                    'clock_out' => $attendance->clock_out ? $attendance->clock_out->format('H:i:s') : 'null',
                    'break_start' => $attendance->break_start ? $attendance->break_start->format('H:i:s') : 'null',
                    'break_end' => $attendance->break_end ? $attendance->break_end->format('H:i:s') : 'null'
                ]);
                
            } catch (\Exception $e) {
                \Log::error('Form: Failed to update attendance record after approval', [
                    'request_id' => $correctionRequest->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        }

        $message = $status === 'approved' ? '申請を承認しました。' : '申請を却下しました。';
        
        return redirect()->route('admin.stamp_correction_request.list')
            ->with('success', $message);
    }

    /**
     * AJAX用の承認処理
     */
    private function processApprovalAjax(Request $request, $id)
    {
        try {
            \Log::info('AJAX approval request received', [
                'request_id' => $id,
                'request_data' => $request->all(),
                'user_id' => Auth::id()
            ]);

            $validatedData = $request->validate([
                'action' => 'required|in:approve,reject',
            ]);

            $correctionRequest = StampCorrectionRequest::with('attendance')->findOrFail($id);

            if ($correctionRequest->status !== 'pending') {
                \Log::warning('Request already processed', [
                    'request_id' => $id,
                    'current_status' => $correctionRequest->status
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => '既に処理済みの申請です。'
                ]);
            }

            $status = $validatedData['action'] === 'approve' ? 'approved' : 'rejected';

            $correctionRequest->update([
                'status' => $status,
                'approved_at' => now(),
                'approved_by' => Auth::id(),
            ]);

            \Log::info('Request status updated', [
                'request_id' => $id,
                'new_status' => $status
            ]);

            // 承認された場合、勤怠記録を更新
            if ($status === 'approved') {
                $attendance = $correctionRequest->attendance;
                $field = $correctionRequest->request_type;
                
                \Log::info('Starting attendance update after approval', [
                    'request_id' => $correctionRequest->id,
                    'attendance_id' => $attendance->id,
                    'field' => $field,
                    'old_value' => $attendance->{$field},
                    'new_value' => $correctionRequest->requested_time
                ]);
                
                try {
                    // 出勤・退勤時刻の更新
                    if (in_array($field, ['clock_in', 'clock_out'])) {
                        $oldValue = $attendance->{$field};
                        $attendance->update([
                            $field => $correctionRequest->requested_time,
                        ]);
                        
                        // 勤務時間を再計算
                        $attendance->update([
                            'total_work_time' => $attendance->calculateWorkTime(),
                        ]);
                        
                        \Log::info('Clock time updated', [
                            'field' => $field,
                            'old' => $oldValue ? $oldValue->format('H:i:s') : 'null',
                            'new' => $correctionRequest->requested_time->format('H:i:s'),
                            'total_work_time' => $attendance->total_work_time
                        ]);
                    }
                    
                    // 休憩時間の更新（新しいBreakTimeモデル対応）
                    elseif (in_array($field, ['break_start', 'break_end'])) {
                        $oldValue = $attendance->{$field};
                        
                        // 古いフィールドも更新（互換性のため）
                        $attendance->update([
                            $field => $correctionRequest->requested_time,
                        ]);
                        
                        // BreakTimeモデルでも更新
                        $this->updateBreakTimeFromRequest($attendance, $field, $correctionRequest->requested_time);
                        
                        // 総休憩時間を再計算
                        $totalBreakMinutes = $attendance->breakTimes()->get()->sum(function($breakTime) {
                            if ($breakTime->start_time && $breakTime->end_time) {
                                return $breakTime->start_time->diffInMinutes($breakTime->end_time);
                            }
                            return 0;
                        });
                        
                        $attendance->update([
                            'total_break_time' => $totalBreakMinutes
                        ]);
                        
                        \Log::info('Break time updated', [
                            'field' => $field,
                            'old' => $oldValue ? $oldValue->format('H:i:s') : 'null',
                            'new' => $correctionRequest->requested_time->format('H:i:s'),
                            'total_break_time' => $totalBreakMinutes
                        ]);
                    }
                    
                    // 備考の更新
                    elseif ($field === 'notes') {
                        $attendance->update([
                            'notes' => $correctionRequest->reason
                        ]);
                        
                        \Log::info('Notes updated', [
                            'old' => $attendance->notes,
                            'new' => $correctionRequest->reason
                        ]);
                    }
                    
                    // 最終的な勤怠データを再読み込み
                    $attendance->refresh();
                    
                    \Log::info('Attendance record successfully updated after approval', [
                        'request_id' => $correctionRequest->id,
                        'attendance_id' => $attendance->id,
                        'field' => $field,
                        'final_value' => $attendance->{$field},
                        'clock_in' => $attendance->clock_in ? $attendance->clock_in->format('H:i:s') : 'null',
                        'clock_out' => $attendance->clock_out ? $attendance->clock_out->format('H:i:s') : 'null',
                        'break_start' => $attendance->break_start ? $attendance->break_start->format('H:i:s') : 'null',
                        'break_end' => $attendance->break_end ? $attendance->break_end->format('H:i:s') : 'null'
                    ]);
                    
                } catch (\Exception $e) {
                    \Log::error('Failed to update attendance record after approval', [
                        'request_id' => $correctionRequest->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
            }

            $message = $status === 'approved' ? '申請を承認しました。' : '申請を却下しました。';
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $status
            ]);

        } catch (\Exception $e) {
            \Log::error('AJAX approval error', [
                'request_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => '処理中にエラーが発生しました: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 承認時に休憩時間を更新するヘルパーメソッド
     */
    private function updateBreakTimeFromRequest($attendance, $field, $requestedTime)
    {
        if ($field === 'break_start') {
            // 休憩開始時刻の更新
            $existingBreak = $attendance->breakTimes()->whereNull('end_time')->first();
            
            if ($existingBreak) {
                // 進行中の休憩がある場合は開始時刻を更新
                $existingBreak->update(['start_time' => $requestedTime]);
            } else {
                // 新しい休憩を作成
                $nextOrder = $attendance->breakTimes()->max('order') + 1;
                \App\Models\BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'start_time' => $requestedTime,
                    'end_time' => null,
                    'order' => $nextOrder ?: 1
                ]);
            }
        } elseif ($field === 'break_end') {
            // 休憩終了時刻の更新
            $activeBreak = $attendance->breakTimes()->whereNull('end_time')->first();
            
            if ($activeBreak) {
                $activeBreak->update(['end_time' => $requestedTime]);
            } else {
                // 進行中の休憩がない場合、最新の休憩の終了時刻を更新
                $latestBreak = $attendance->breakTimes()->orderBy('order', 'desc')->first();
                if ($latestBreak) {
                    $latestBreak->update(['end_time' => $requestedTime]);
                }
            }
        }
    }
}
