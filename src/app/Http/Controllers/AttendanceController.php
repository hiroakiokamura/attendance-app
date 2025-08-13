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
    public function detail($id)
    {
        $user = Auth::user();
        
        $attendance = Attendance::where('user_id', $user->id)
            ->findOrFail($id);

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
