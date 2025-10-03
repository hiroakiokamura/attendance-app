<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * PG03: 勤怠登録画面
     */
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();
        
        // 今日の勤怠記録を取得
        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->with('breakTimes')
            ->first();

        return view('attendance.index', compact('attendance'));
    }

    /**
     * 出勤打刻
     */
    public function clockIn(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();
        $now = Carbon::now();

        // 既に今日の勤怠記録があるかチェック
        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

        if ($attendance && $attendance->clock_in) {
            return back()->with('error', '既に出勤済みです。');
        }

        // 勤怠記録を作成または更新
        $attendance = Attendance::updateOrCreate(
            [
                'user_id' => $user->id,
                'work_date' => $today,
            ],
            [
                'clock_in' => $now,
            ]
        );

        return back()->with('success', '出勤を記録しました。');
    }

    /**
     * 退勤打刻
     */
    public function clockOut(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();
        $now = Carbon::now();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

        if (!$attendance || !$attendance->clock_in) {
            return back()->with('error', '出勤記録がありません。');
        }

        if ($attendance->clock_out) {
            return back()->with('error', '既に退勤済みです。');
        }

        // 退勤時刻を記録し、勤務時間を計算
        $attendance->update([
            'clock_out' => $now,
            'total_work_time' => $attendance->calculateWorkTime(),
        ]);

        return back()->with('success', '退勤を記録しました。');
    }

    /**
     * 休憩開始
     */
    public function breakStart(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();
        $now = Carbon::now();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->with('breakTimes')
            ->first();

        if (!$attendance || !$attendance->clock_in) {
            return back()->with('error', '出勤記録がありません。');
        }

        // 現在進行中の休憩があるかチェック
        $activeBreak = $attendance->breakTimes()->whereNull('end_time')->first();
        if ($activeBreak) {
            return back()->with('error', '既に休憩中です。');
        }

        // 新しい休憩時間を作成
        $nextOrder = $attendance->breakTimes()->max('order');
        $nextOrder = $nextOrder ? $nextOrder + 1 : 1;
        
        \App\Models\BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => $now,
            'end_time' => null,
            'order' => $nextOrder
        ]);

        return back()->with('success', '休憩を開始しました。');
    }

    /**
     * 休憩終了
     */
    public function breakEnd(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();
        $now = Carbon::now();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->with('breakTimes')
            ->first();

        if (!$attendance) {
            return back()->with('error', '勤怠記録がありません。');
        }

        // 現在進行中の休憩を取得
        $activeBreak = $attendance->breakTimes()->whereNull('end_time')->first();
        if (!$activeBreak) {
            return back()->with('error', '休憩開始記録がありません。');
        }

        // 休憩終了時刻を設定
        $activeBreak->update([
            'end_time' => $now
        ]);

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

        return back()->with('success', '休憩を終了しました。');
    }

    /**
     * PG04: 勤怠一覧画面
     */
    public function list(Request $request)
    {
        $user = Auth::user();
        $query = Attendance::where('user_id', $user->id);

        // 月フィルター
        $currentMonth = $request->month ?? now()->format('Y-m');
        if ($request->month) {
            $year = substr($request->month, 0, 4);
            $month = substr($request->month, 5, 2);
            $query->whereYear('work_date', $year)
                  ->whereMonth('work_date', $month);
        } else {
            // デフォルトは今月
            $query->whereMonth('work_date', now()->month)
                  ->whereYear('work_date', now()->year);
        }
        
        $attendances = $query->orderBy('work_date', 'desc')
            ->paginate(20);

        // 前月・翌月の計算
        $currentDate = Carbon::parse($currentMonth . '-01');
        $prevMonth = $currentDate->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentDate->copy()->addMonth()->format('Y-m');

        return view('attendance.list', compact('attendances', 'currentMonth', 'prevMonth', 'nextMonth'));
    }

    /**
     * PG05: 勤怠詳細画面
     */
    public function detail(Request $request, $id)
    {
        $user = Auth::user();
        
        $attendance = Attendance::where('user_id', $user->id)
            ->with('breakTimes')
            ->findOrFail($id);

        // 申請一覧からの遷移で承認待ち状態を表示
        if ($request->has('pending') || $request->get('request_status') === 'pending') {
            session()->flash('pending_request', true);
            
            // 最新の承認待ち申請データを取得して入力フィールドに反映
            $pendingRequests = \App\Models\StampCorrectionRequest::where('attendance_id', $id)
                ->where('status', 'pending')
                ->get();
            
            $inputData = [];
            foreach ($pendingRequests as $pendingRequest) {
                if ($pendingRequest->request_type === 'clock_in') {
                    $inputData['clock_in'] = $pendingRequest->requested_time->format('H:i');
                } elseif ($pendingRequest->request_type === 'clock_out') {
                    $inputData['clock_out'] = $pendingRequest->requested_time->format('H:i');
                } elseif ($pendingRequest->request_type === 'break_start') {
                    $inputData['break_start'] = $pendingRequest->requested_time->format('H:i');
                } elseif ($pendingRequest->request_type === 'break_end') {
                    $inputData['break_end'] = $pendingRequest->requested_time->format('H:i');
                } elseif ($pendingRequest->request_type === 'break_times') {
                    // 休憩時間の変更を反映
                    $breakData = json_decode($pendingRequest->requested_time, true);
                    $inputData['break_times'] = $breakData;
                } elseif ($pendingRequest->request_type === 'multiple_changes') {
                    // 複数項目の変更を反映
                    $changes = json_decode($pendingRequest->requested_time, true);
                    foreach ($changes as $change) {
                        if (in_array($change['field'], ['clock_in', 'clock_out', 'break_start', 'break_end'])) {
                            $inputData[$change['field']] = \Carbon\Carbon::parse($change['requested'])->format('H:i');
                        } elseif ($change['field'] === 'break_times') {
                            $inputData['break_times'] = json_decode($change['requested'], true);
                        }
                    }
                }
                // 備考は最新の申請の理由を使用
                $inputData['notes'] = $pendingRequest->reason;
            }
            
            if (!empty($inputData)) {
                session()->flash('_old_input', $inputData);
            }
        }

        // 申請ステータスに基づく状態設定
        if ($request->has('request_status')) {
            $requestStatus = $request->get('request_status');
            if ($requestStatus === 'approved') {
                session()->flash('approved_request', true);
            } elseif ($requestStatus === 'pending') {
                session()->flash('pending_request', true);
            }
        }

        return view('attendance.detail', compact('attendance'));
    }

    /**
     * 勤怠編集画面
     */
    public function edit($id)
    {
        $user = Auth::user();
        
        $attendance = Attendance::where('user_id', $user->id)
            ->findOrFail($id);

        return view('attendance.edit', compact('attendance'));
    }

    /**
     * 勤怠更新処理
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        $attendance = Attendance::where('user_id', $user->id)
            ->findOrFail($id);

        $request->validate([
            'clock_in' => 'required|date_format:H:i',
            'clock_out' => 'required|date_format:H:i|after:clock_in',
            'break_start' => 'nullable|date_format:H:i|after:clock_in|before:clock_out',
            'break_end' => 'nullable|date_format:H:i|after:break_start|before:clock_out',
            'notes' => 'required|string|max:1000',
        ], [
            'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'break_start.after' => '休憩時間が不適切な値です',
            'break_start.before' => '休憩時間が不適切な値です',
            'break_end.after' => '休憩時間もしくは退勤時間が不適切な値です',
            'break_end.before' => '休憩時間もしくは退勤時間が不適切な値です',
            'notes.required' => '備考を記入してください',
        ]);

        // 時刻を作成
        $workDate = $attendance->work_date;
        $clockIn = Carbon::parse($workDate->format('Y-m-d') . ' ' . $request->clock_in);
        $clockOut = Carbon::parse($workDate->format('Y-m-d') . ' ' . $request->clock_out);
        
        $breakStart = $request->break_start ? Carbon::parse($workDate->format('Y-m-d') . ' ' . $request->break_start) : null;
        $breakEnd = $request->break_end ? Carbon::parse($workDate->format('Y-m-d') . ' ' . $request->break_end) : null;

        // 休憩時間を計算
        $totalBreakTime = 0;
        if ($breakStart && $breakEnd) {
            $totalBreakTime = $breakStart->diffInMinutes($breakEnd);
        }

        // 勤務時間を計算
        $totalWorkTime = $clockIn->diffInMinutes($clockOut) - $totalBreakTime;

        $attendance->update([
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'break_start' => $breakStart,
            'break_end' => $breakEnd,
            'total_work_time' => $totalWorkTime,
            'total_break_time' => $totalBreakTime,
            'notes' => $request->notes,
        ]);

        return redirect()->route('attendance.detail', $attendance->id)
            ->with('success', '勤怠情報を更新しました。');
    }
}
