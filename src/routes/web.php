<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\Admin\AdminController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;

// ホーム画面をログイン画面にリダイレクト
Route::get('/', function () {
    return redirect()->route('login');
});

// 一般ユーザー用ルート
Route::middleware('guest')->group(function () {
    // PG01: 会員登録画面
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
    
    // PG02: ログイン画面（一般ユーザー）
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

// 認証済み一般ユーザー用ルート
Route::middleware(['auth', 'verified'])->group(function () {
    // PG03: 勤怠登録画面
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock-in');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock-out');
    Route::post('/attendance/break-start', [AttendanceController::class, 'breakStart'])->name('attendance.break-start');
    Route::post('/attendance/break-end', [AttendanceController::class, 'breakEnd'])->name('attendance.break-end');
    
    // PG04: 勤怠一覧画面
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    
    // PG05: 勤怠詳細画面
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail'])->name('attendance.detail');
    
    // PG06: 申請一覧画面（一般ユーザー）
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'list'])->name('stamp_correction_request.list');
    Route::get('/stamp_correction_request/create/{attendance}', [StampCorrectionRequestController::class, 'create'])->name('stamp_correction_request.create');
    Route::post('/stamp_correction_request', [StampCorrectionRequestController::class, 'store'])->name('stamp_correction_request.store');
    
    // ログアウト
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

// 管理者用ルート
Route::prefix('admin')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        // PG07: ログイン画面（管理者）
        Route::get('/login', [AdminController::class, 'showLoginForm'])->name('admin.login');
        Route::post('/login', [AdminController::class, 'login'])->name('admin.login.submit');
    });
    
    Route::middleware(['auth', 'admin'])->group(function () {
        // PG08: 勤怠一覧画面（管理者）
        Route::get('/attendance/list', [AdminController::class, 'attendanceList'])->name('admin.attendance.list');
        
        // PG09: 勤怠詳細画面（管理者）
        Route::get('/attendance/{id}', [AdminController::class, 'attendanceDetail'])->name('admin.attendance.detail');
        
        // PG10: スタッフ一覧画面（管理者）
        Route::get('/staff/list', [AdminController::class, 'staffList'])->name('admin.staff.list');
        
        // PG11: スタッフ別勤怠一覧画面（管理者）
        Route::get('/attendance/staff/{id}', [AdminController::class, 'staffAttendanceList'])->name('admin.attendance.staff');
        
        // PG12: 申請一覧画面（管理者）
        Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'adminList'])->name('admin.stamp_correction_request.list');
        
        // PG13: 修正申請承認画面（管理者）
        Route::get('/stamp_correction_request/approve/{attendance_correct_request}', [StampCorrectionRequestController::class, 'approve'])->name('admin.stamp_correction_request.approve');
        Route::post('/stamp_correction_request/approve/{attendance_correct_request}', [StampCorrectionRequestController::class, 'processApproval'])->name('admin.stamp_correction_request.process');
        
        // 管理者ログアウト
        Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
    });
});
