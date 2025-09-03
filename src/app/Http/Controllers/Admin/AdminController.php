<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
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
        $attendance = Attendance::with(['user', 'stampCorrectionRequests'])
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

        $attendances = $query->orderBy('work_date', 'desc')
            ->paginate(31);

        // 前月・翌月の計算
        $currentDate = Carbon::parse($currentMonth . '-01');
        $prevMonth = $currentDate->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentDate->copy()->addMonth()->format('Y-m');

        return view('admin.attendance.staff', compact('user', 'attendances', 'currentMonth', 'prevMonth', 'nextMonth'));
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

        return redirect()->route('admin.attendance.detail', $attendance->id)
            ->with('success', '勤怠情報を更新しました。');
    }
}
