<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class ValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.timezone' => 'Asia/Tokyo']);
        config(['app.locale' => 'ja']);
    }

    /** @test */
    public function test_user_registration_validation_name_required()
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['name' => 'お名前を入力してください']);
    }

    /** @test */
    public function test_user_registration_validation_email_required()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /** @test */
    public function test_user_registration_validation_password_required()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /** @test */
    public function test_user_registration_validation_password_min_length()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '123', // 8文字未満
            'password_confirmation' => '123',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入力してください']);
    }

    /** @test */
    public function test_user_registration_validation_password_confirmation()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードと一致しません']);
    }

    /** @test */
    public function test_login_validation_failed_message()
    {
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function test_admin_login_validation_email_required()
    {
        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /** @test */
    public function test_admin_login_validation_password_required()
    {
        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /** @test */
    public function test_stamp_correction_request_validation_notes_required()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post('/stamp_correction_request/from_detail', [
            'attendance_id' => $attendance->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'notes' => '', // 空の備考
        ]);

        $response->assertSessionHasErrors(['notes' => '備考を記入してください']);
    }

    /** @test */
    public function test_stamp_correction_request_validation_clock_in_format()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post('/stamp_correction_request/from_detail', [
            'attendance_id' => $attendance->id,
            'clock_in' => '9:00', // HH:MM形式ではない
            'notes' => '修正申請',
        ]);

        $response->assertSessionHasErrors(['clock_in']);
    }

    /** @test */
    public function test_stamp_correction_request_validation_clock_out_format()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post('/stamp_correction_request/from_detail', [
            'attendance_id' => $attendance->id,
            'clock_out' => '18:0', // HH:MM形式ではない
            'notes' => '修正申請',
        ]);

        $response->assertSessionHasErrors(['clock_out']);
    }

    /** @test */
    public function test_stamp_correction_request_validation_break_time_format()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post('/stamp_correction_request/from_detail', [
            'attendance_id' => $attendance->id,
            'break_times' => [
                ['start_time' => '12:0', 'end_time' => '13:00'] // HH:MM形式ではない
            ],
            'notes' => '修正申請',
        ]);

        $response->assertSessionHasErrors(['break_times.0.start_time']);
    }

    /** @test */
    public function test_stamp_correction_request_custom_validation_clock_in_after_clock_out()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post('/stamp_correction_request/from_detail', [
            'attendance_id' => $attendance->id,
            'clock_in' => '19:00', // 退勤時刻より後
            'clock_out' => '18:00',
            'notes' => '修正申請',
        ]);

        $response->assertSessionHasErrors(['clock_in' => '出勤時間は退勤時間より前の時刻を入力してください']);
    }

    /** @test */
    public function test_stamp_correction_request_custom_validation_break_time_after_clock_out()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post('/stamp_correction_request/from_detail', [
            'attendance_id' => $attendance->id,
            'clock_out' => '18:00',
            'break_times' => [
                ['start_time' => '19:00', 'end_time' => '20:00'] // 退勤時刻より後
            ],
            'notes' => '修正申請',
        ]);

        $response->assertSessionHasErrors(['break_times.0.end_time' => '休憩終了時間は退勤時間以前の時刻を入力してください']);
    }

    /** @test */
    public function test_stamp_correction_request_custom_validation_break_start_after_break_end()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post('/stamp_correction_request/from_detail', [
            'attendance_id' => $attendance->id,
            'break_times' => [
                ['start_time' => '13:00', 'end_time' => '12:00'] // 開始時刻が終了時刻より後
            ],
            'notes' => '修正申請',
        ]);

        $response->assertSessionHasErrors(['break_times.0.start_time' => '休憩開始時間は休憩終了時間より前の時刻を入力してください']);
    }

    /** @test */
    public function test_admin_attendance_update_validation_notes_required()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($admin)->put("/admin/attendance/{$attendance->id}", [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'notes' => '', // 空の備考
        ]);

        $response->assertSessionHasErrors(['notes' => '備考を記入してください']);
    }

    /** @test */
    public function test_admin_attendance_update_custom_validation_clock_in_after_clock_out()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($admin)->put("/admin/attendance/{$attendance->id}", [
            'clock_in' => '19:00', // 退勤時刻より後
            'clock_out' => '18:00',
            'notes' => '管理者による修正',
        ]);

        $response->assertSessionHasErrors(['clock_in' => '出勤時間は退勤時間より前の時刻を入力してください']);
    }

    /** @test */
    public function test_admin_attendance_update_custom_validation_break_time_invalid()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($admin)->put("/admin/attendance/{$attendance->id}", [
            'clock_out' => '18:00',
            'break_times' => [
                ['start_time' => '19:00', 'end_time' => '20:00'] // 退勤時刻より後
            ],
            'notes' => '管理者による修正',
        ]);

        $response->assertSessionHasErrors(['break_times.0.end_time' => '休憩終了時間は退勤時間以前の時刻を入力してください']);
    }

    /** @test */
    public function test_stamp_correction_request_validation_break_time_overlap()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post('/stamp_correction_request/from_detail', [
            'attendance_id' => $attendance->id,
            'break_times' => [
                ['start_time' => '12:00', 'end_time' => '13:00'],
                ['start_time' => '12:30', 'end_time' => '13:30'] // 重複
            ],
            'notes' => '修正申請',
        ]);

        $response->assertSessionHasErrors(['break_times.1.start_time' => '休憩時間が重複しています']);
    }

    /** @test */
    public function test_stamp_correction_request_validation_break_before_clock_in()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post('/stamp_correction_request/from_detail', [
            'attendance_id' => $attendance->id,
            'clock_in' => '09:00',
            'break_times' => [
                ['start_time' => '08:00', 'end_time' => '08:30'] // 出勤時間より前
            ],
            'notes' => '修正申請',
        ]);

        $response->assertSessionHasErrors(['break_times.0.start_time' => '休憩開始時間は出勤時間以降の時刻を入力してください']);
    }

    /** @test */
    public function test_password_reset_validation_email_required()
    {
        $response = $this->post('/forgot-password', [
            'email' => '',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function test_password_reset_validation_email_format()
    {
        $response = $this->post('/forgot-password', [
            'email' => 'invalid-email',
        ]);

        $response->assertSessionHasErrors(['email']);
    }
}
