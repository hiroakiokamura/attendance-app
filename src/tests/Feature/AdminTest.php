<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.timezone' => 'Asia/Tokyo']);
    }

    private function createAdmin()
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function createUser()
    {
        return User::factory()->create(['is_admin' => false]);
    }

    /** @test */
    public function test_admin_login_screen_can_be_rendered()
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
        $response->assertViewIs('admin.login');
    }

    /** @test */
    public function test_admin_can_login()
    {
        $admin = $this->createAdmin();

        $response = $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/attendance/list');
        $this->assertAuthenticatedAs($admin);
    }

    /** @test */
    public function test_non_admin_user_cannot_login_to_admin()
    {
        $user = $this->createUser();

        $response = $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHas('error', '管理者権限がありません。');
        $this->assertGuest();
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
    public function test_admin_login_with_invalid_credentials()
    {
        $response = $this->post('/admin/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHas('error', 'ログイン情報が登録されていません。');
    }

    /** @test */
    public function test_admin_can_view_attendance_list()
    {
        $admin = $this->createAdmin();
        $users = User::factory()->count(3)->create(['is_admin' => false]);
        
        foreach ($users as $user) {
            Attendance::factory()->create([
                'user_id' => $user->id,
                'work_date' => Carbon::today(),
            ]);
        }

        $response = $this->actingAs($admin)->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertViewIs('admin.attendance.list');
        $response->assertViewHas('attendances');
    }

    /** @test */
    public function test_admin_can_view_attendance_detail()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($admin)->get("/admin/attendance/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertViewIs('admin.attendance.detail');
        $response->assertViewHas('attendance', $attendance);
    }

    /** @test */
    public function test_admin_can_view_staff_list()
    {
        $admin = $this->createAdmin();
        User::factory()->count(5)->create(['is_admin' => false]);

        $response = $this->actingAs($admin)->get('/admin/staff/list');

        $response->assertStatus(200);
        $response->assertViewIs('admin.staff.list');
        $response->assertViewHas('users');
    }

    /** @test */
    public function test_admin_can_search_staff_by_name()
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create([
            'name' => '田中太郎',
            'is_admin' => false,
        ]);

        $response = $this->actingAs($admin)->get('/admin/staff/list?search=田中');

        $response->assertStatus(200);
        $response->assertViewIs('admin.staff.list');
    }

    /** @test */
    public function test_admin_can_view_staff_attendance_list()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        Attendance::factory()->count(10)->create(['user_id' => $user->id]);

        $response = $this->actingAs($admin)->get("/admin/attendance/staff/{$user->id}");

        $response->assertStatus(200);
        $response->assertViewIs('admin.attendance.staff');
        $response->assertViewHas(['user', 'attendances']);
    }

    /** @test */
    public function test_admin_can_export_staff_attendance_csv()
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create([
            'name' => '田中太郎',
            'is_admin' => false,
        ]);
        Attendance::factory()->count(5)->create(['user_id' => $user->id]);

        $response = $this->actingAs($admin)->get("/admin/attendance/staff/{$user->id}?format=csv");

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('田中太郎_勤怠_', $response->headers->get('content-disposition'));
    }

    /** @test */
    public function test_admin_can_view_stamp_correction_request_list()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        StampCorrectionRequest::factory()->count(3)->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
        ]);

        $response = $this->actingAs($admin)->get('/admin/stamp_correction_request/list');

        $response->assertStatus(200);
        $response->assertViewIs('admin.stamp_correction_request.list');
        $response->assertViewHas('requests');
    }

    /** @test */
    public function test_admin_can_view_stamp_correction_request_approve_screen()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        $request = StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get("/admin/stamp_correction_request/approve/{$request->id}");

        $response->assertStatus(200);
        $response->assertViewIs('admin.stamp_correction_request.approve');
        $response->assertViewHas(['request', 'attendanceData']);
    }

    /** @test */
    public function test_admin_can_approve_stamp_correction_request()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::now()->setTime(9, 0),
        ]);
        $request = StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'request_type' => 'clock_in',
            'requested_time' => Carbon::now()->setTime(8, 30),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->postJson("/admin/stamp_correction_request/approve/{$request->id}", [
                'action' => 'approve',
                'admin_comment' => '承認しました',
            ]);

        $response->assertJson(['success' => true]);
        
        $request->refresh();
        $this->assertEquals('approved', $request->status);
        $this->assertEquals($admin->id, $request->approved_by);
        
        $attendance->refresh();
        $this->assertEquals('08:30', $attendance->clock_in->format('H:i'));
    }

    /** @test */
    public function test_admin_can_reject_stamp_correction_request()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        $request = StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->postJson("/admin/stamp_correction_request/approve/{$request->id}", [
                'action' => 'reject',
                'admin_comment' => '却下しました',
            ]);

        $response->assertJson(['success' => true]);
        
        $request->refresh();
        $this->assertEquals('rejected', $request->status);
        $this->assertEquals($admin->id, $request->approved_by);
    }

    /** @test */
    public function test_non_admin_user_cannot_access_admin_routes()
    {
        $user = $this->createUser();

        $routes = [
            '/admin/attendance/list',
            '/admin/staff/list',
            '/admin/stamp_correction_request/list',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($user)->get($route);
            $response->assertStatus(403);
        }
    }

    /** @test */
    public function test_guest_user_redirected_to_admin_login()
    {
        $response = $this->get('/admin/attendance/list');

        $response->assertRedirect('/admin/login');
    }

    /** @test */
    public function test_admin_can_update_attendance()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'clock_in' => '08:30',
            'clock_out' => '17:30',
            'break_times' => [
                ['start_time' => '12:00', 'end_time' => '13:00']
            ],
            'notes' => '管理者による修正',
        ];

        $response = $this->actingAs($admin)->put("/admin/attendance/{$attendance->id}", $updateData);

        $response->assertRedirect("/admin/attendance/{$attendance->id}");
        
        $attendance->refresh();
        $this->assertEquals('08:30', $attendance->clock_in->format('H:i'));
        $this->assertEquals('17:30', $attendance->clock_out->format('H:i'));
        $this->assertEquals('管理者による修正', $attendance->notes);
    }

    /** @test */
    public function test_admin_attendance_update_validation_notes_required()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'notes' => '', // 空の備考
        ];

        $response = $this->actingAs($admin)->put("/admin/attendance/{$attendance->id}", $updateData);

        $response->assertSessionHasErrors(['notes' => '備考を記入してください']);
    }
}
