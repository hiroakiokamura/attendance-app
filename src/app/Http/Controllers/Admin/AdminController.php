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

        return back()->with('error', 'ログイン情報が正しくありません。');
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
        $query = Attendance::with('user');

        // 日付フィルター
        if ($request->date) {
            $query->where('work_date', $request->date);
        }

        // ユーザーフィルター
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $attendances = $query->orderBy('work_date', 'desc')
            ->orderBy('clock_in', 'desc')
            ->paginate(20);

        $users = User::where('is_admin', false)->get();

        return view('admin.attendance.list', compact('attendances', 'users'));
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
        if ($request->month) {
            $query->whereYear('work_date', substr($request->month, 0, 4))
                  ->whereMonth('work_date', substr($request->month, 5, 2));
        }

        $attendances = $query->orderBy('work_date', 'desc')
            ->paginate(31);

        return view('admin.attendance.staff', compact('user', 'attendances'));
    }
}
