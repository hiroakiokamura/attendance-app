<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * PG07: ログイン画面（管理者）
     */
    public function showLoginForm()
    {
        return view('admin.login');
    }

    /**
     * 管理者ログイン処理
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        
        // 管理者フラグがtrueのユーザーのみログイン可能
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            if ($user->is_admin) {
                $request->session()->regenerate();
                return redirect()->intended(route('admin.attendance.list'));
            } else {
                Auth::logout();
                return back()->with('error', '管理者権限がありません。');
            }
        }

        return back()->with('error', 'ログイン情報が登録されていません。');
    }

    /**
     * 管理者ログアウト
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('admin.login');
    }

    /**
     * PG08: 勤怠一覧画面（管理者）
     */
    public function attendanceList(Request $request)
    {
        // 現在の日付を取得（リクエストから日付が指定されている場合はその日付を使用）
        $currentDate = $request->date ? Carbon::parse($request->date) : now();
        
        // 前日・翌日の日付を計算
        $prevDate = $currentDate->copy()->subDay()->format('Y-m-d');
        $nextDate = $currentDate->copy()->addDay()->format('Y-m-d');

        // その日の勤怠記録を取得
        $attendances = Attendance::with('user')
            ->whereDate('work_date', $currentDate->format('Y-m-d'))
            ->orderBy('work_date', 'desc')
            ->get();

        return view('admin.attendance.list', compact('attendances', 'currentDate', 'prevDate', 'nextDate'));
    }

    /**
     * PG09: 勤怠詳細画面（管理者）
     */
    public function attendanceDetail($id)
    {
        $attendance = Attendance::with(['user', 'stampCorrectionRequests', 'breakTimes'])
            ->findOrFail($id);

        return view('admin.attendance.detail', compact('attendance'));
    }

    /**
     * PG10: スタッフ一覧画面（管理者）
     */
    public function staffList(Request $request)
    {
        $query = User::where('is_admin', false);

        // 名前検索
        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        $users = $query->withCount('attendances')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.staff.list', compact('users'));
    }

    /**
     * PG11: スタッフ別勤怠一覧画面（管理者）
     */
    public function staffAttendanceList($id, Request $request)
    {
        $user = User::where('is_admin', false)->findOrFail($id);
        
        $query = Attendance::where('user_id', $user->id);

        // 月フィルター
        $currentMonth = $request->month ?? now()->format('Y-m');
        if ($request->month) {
            $query->whereYear('work_date', substr($request->month, 0, 4))
                  ->whereMonth('work_date', substr($request->month, 5, 2));
        } else {
            // デフォルトは今月
            $query->whereMonth('work_date', now()->month)
                  ->whereYear('work_date', now()->year);
        }

        $attendances = $query->orderBy('work_date', 'asc')->get();

        // 前月・翌月の計算
        $currentDate = Carbon::parse($currentMonth . '-01');
        $prevMonth = $currentDate->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentDate->copy()->addMonth()->format('Y-m');

        // CSV出力の場合
        if ($request->format === 'csv') {
            return $this->exportAttendanceCsv($user, $attendances, $currentMonth);
        }

        return view('admin.attendance.staff', compact('user', 'attendances', 'currentMonth', 'prevMonth', 'nextMonth'));
    }

    /**
     * 勤怠データをCSV形式で出力
     */
    private function exportAttendanceCsv($user, $attendances, $currentMonth)
    {
        $filename = $user->name . '_勤怠_' . $currentMonth . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($user, $attendances, $currentMonth) {
            $file = fopen('php://output', 'w');
            
            // BOM付きUTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // ヘッダー行
            fputcsv($file, ['日付', '出勤時刻', '退勤時刻', '休憩時間', '勤務時間']);
            
            // 指定月の全日付を生成
            $startDate = Carbon::parse($currentMonth . '-01');
            $endDate = $startDate->copy()->endOfMonth();
            $attendancesByDate = $attendances->keyBy(function($item) {
                return $item->work_date->format('Y-m-d');
            });
            
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                $dateStr = $date->format('Y-m-d');
                $attendance = $attendancesByDate->get($dateStr);
                
                fputcsv($file, [
                    $date->format('m/d(D)'),
                    $attendance && $attendance->clock_in ? $attendance->clock_in->format('H:i') : '',
                    $attendance && $attendance->clock_out ? $attendance->clock_out->format('H:i') : '',
                    $attendance ? $attendance->formatted_break_time : '',
                    $attendance ? $attendance->formatted_work_time : '',
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * 勤怠詳細編集画面（管理者）
     */
    public function editAttendance($id)
    {
        $attendance = Attendance::with('user')->findOrFail($id);
        return view('admin.attendance.edit', compact('attendance'));
    }

    /**
     * 勤怠詳細更新処理（管理者）
     */
    public function updateAttendance(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $request->validate([
            'clock_in' => 'nullable|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i',
            'break_times.*.start_time' => 'nullable|date_format:H:i',
            'break_times.*.end_time' => 'nullable|date_format:H:i',
            'notes' => 'required|string|max:1000',
        ], [
            'clock_in.date_format' => '出勤時刻の形式が正しくありません（HH:MM）',
            'clock_out.date_format' => '退勤時刻の形式が正しくありません（HH:MM）',
            'break_times.*.start_time.date_format' => '休憩開始時刻の形式が正しくありません（HH:MM）',
            'break_times.*.end_time.date_format' => '休憩終了時刻の形式が正しくありません（HH:MM）',
            'notes.required' => '備考を記入してください',
            'notes.max' => '備考は1000文字以内で入力してください',
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

        // 時刻を作成
        $workDate = $attendance->work_date;
        $clockIn = $request->clock_in ? Carbon::parse($workDate->format('Y-m-d') . ' ' . $request->clock_in) : null;
        $clockOut = $request->clock_out ? Carbon::parse($workDate->format('Y-m-d') . ' ' . $request->clock_out) : null;

        // 休憩時間の処理
        $breakTimes = $request->break_times ?? [];
        $totalBreakTime = 0;
        
        // 既存の休憩時間をクリア
        $attendance->breakTimes()->delete();
        
        // 新しい休憩時間を保存
        foreach ($breakTimes as $index => $breakTime) {
            if (!empty($breakTime['start_time']) && !empty($breakTime['end_time'])) {
                $startDateTime = Carbon::parse($workDate->format('Y-m-d') . ' ' . $breakTime['start_time']);
                $endDateTime = Carbon::parse($workDate->format('Y-m-d') . ' ' . $breakTime['end_time']);
                
                \App\Models\BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'start_time' => $startDateTime,
                    'end_time' => $endDateTime,
                    'order' => $index + 1
                ]);
                
                $totalBreakTime += $startDateTime->diffInMinutes($endDateTime);
            }
        }

        // 勤務時間を計算
        $totalWorkTime = null;
        if ($clockIn && $clockOut) {
            $totalWorkMinutes = $clockIn->diffInMinutes($clockOut) - $totalBreakTime;
            $totalWorkTime = max(0, $totalWorkMinutes);
        }

        $attendance->update([
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'total_work_time' => $totalWorkTime,
            'total_break_time' => $totalBreakTime,
            'notes' => $request->notes,
        ]);

        return redirect()->route('admin.attendance.detail', $attendance->id)
            ->with('success', '勤怠情報を更新しました。')
            ->withInput();
    }
}
