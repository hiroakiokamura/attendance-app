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
            ->first();

        if (!$attendance || !$attendance->clock_in) {
            return back()->with('error', '出勤記録がありません。');
        }

        if ($attendance->break_start && !$attendance->break_end) {
            return back()->with('error', '既に休憩中です。');
        }

        $attendance->update([
            'break_start' => $now,
            'break_end' => null,
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
            ->first();

        if (!$attendance || !$attendance->break_start) {
            return back()->with('error', '休憩開始記録がありません。');
        }

        if ($attendance->break_end) {
            return back()->with('error', '既に休憩終了済みです。');
        }

        // 休憩時間を計算
        $breakTime = $attendance->calculateBreakTime();
        $totalBreakTime = ($attendance->total_break_time ?? 0) + $breakTime;

        $attendance->update([
            'break_end' => $now,
            'total_break_time' => $totalBreakTime,
        ]);

        return back()->with('success', '休憩を終了しました。');
    }

    /**
     * PG04: 勤怠一覧画面
     */
    public function list(Request $request)
    {
        $user = Auth::user();
        
        $attendances = Attendance::where('user_id', $user->id)
            ->orderBy('work_date', 'desc')
            ->paginate(20);

        return view('attendance.list', compact('attendances'));
    }

    /**
     * PG05: 勤怠詳細画面
     */
    public function detail($id)
    {
        $user = Auth::user();
        
        $attendance = Attendance::where('user_id', $user->id)
            ->findOrFail($id);

        return view('attendance.detail', compact('attendance'));
    }
}
