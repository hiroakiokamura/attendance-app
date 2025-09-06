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
    public function list()
    {
        $user = Auth::user();
        
        $requests = StampCorrectionRequest::with(['attendance'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('stamp_correction_request.list', compact('requests'));
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
            'break_start' => 'nullable|date_format:H:i',
            'break_end' => 'nullable|date_format:H:i',
            'break2_start' => 'nullable|date_format:H:i',
            'break2_end' => 'nullable|date_format:H:i',
            'notes' => 'required|string|max:1000',
        ]);

        $attendance = Attendance::findOrFail($request->attendance_id);
        
        // 自分の勤怠記録かチェック
        if ($attendance->user_id !== Auth::id()) {
            abort(403);
        }

        $changes = [];
        $reason = $request->notes;

        // 各フィールドの変更をチェック
        $fields = [
            'clock_in' => '出勤時刻',
            'clock_out' => '退勤時刻',
            'break_start' => '休憩開始時刻',
            'break_end' => '休憩終了時刻'
        ];

        foreach ($fields as $field => $label) {
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

        // 変更がない場合はエラー
        if (empty($changes)) {
            return back()->withErrors(['修正する項目がありません。']);
        }

        // 各変更に対して修正申請を作成
        foreach ($changes as $change) {
            $requestedDateTime = Carbon::parse($attendance->work_date->format('Y-m-d') . ' ' . $change['requested']);

            StampCorrectionRequest::create([
                'user_id' => Auth::id(),
                'attendance_id' => $attendance->id,
                'request_type' => $change['field'],
                'original_time' => $change['original'],
                'requested_time' => $requestedDateTime,
                'reason' => $reason,
                'status' => 'pending',
            ]);
        }

        return redirect()->route('stamp_correction_request.list')
            ->with('success', count($changes) . '件の修正申請を送信しました。');
    }

    /**
     * PG12: 申請一覧画面（管理者）
     */
    public function adminList(Request $request)
    {
        $query = StampCorrectionRequest::with(['user', 'attendance']);

        // ステータスフィルター
        if ($request->status) {
            $query->where('status', $request->status);
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
        $request = StampCorrectionRequest::with(['user', 'attendance'])
            ->findOrFail($id);

        return view('admin.stamp_correction_request.approve', compact('request'));
    }

    /**
     * 修正申請承認処理
     */
    public function processApproval(Request $request, $id)
    {
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
            'admin_comment' => $validatedData['admin_comment'],
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        // 承認された場合、勤怠記録を更新
        if ($status === 'approved') {
            $attendance = $correctionRequest->attendance;
            $field = $correctionRequest->request_type;
            
            $attendance->update([
                $field => $correctionRequest->requested_time,
            ]);

            // 勤務時間を再計算
            if (in_array($field, ['clock_in', 'clock_out'])) {
                $attendance->update([
                    'total_work_time' => $attendance->calculateWorkTime(),
                ]);
            }

            // 休憩時間を再計算
            if (in_array($field, ['break_start', 'break_end'])) {
                $attendance->update([
                    'total_break_time' => $attendance->calculateBreakTime(),
                ]);
            }
        }

        $message = $status === 'approved' ? '申請を承認しました。' : '申請を却下しました。';
        
        return redirect()->route('admin.stamp_correction_request.list')
            ->with('success', $message);
    }
}
