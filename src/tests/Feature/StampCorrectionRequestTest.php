<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class StampCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.timezone' => 'Asia/Tokyo']);
    }

    /** @test */
    public function test_user_can_view_stamp_correction_request_list()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        StampCorrectionRequest::factory()->count(3)->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
        ]);

        $response = $this->actingAs($user)->get('/stamp_correction_request/list');

        $response->assertStatus(200);
        $response->assertViewIs('stamp_correction_request.list');
        $response->assertViewHas('requests');
    }

    /** @test */
    public function test_user_can_submit_stamp_correction_request_from_detail()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::now()->setTime(9, 0),
            'clock_out' => Carbon::now()->setTime(18, 0),
        ]);

        $requestData = [
            'attendance_id' => $attendance->id,
            'clock_in' => '08:30',
            'clock_out' => '17:30',
            'break_times' => [
                ['start_time' => '12:00', 'end_time' => '13:00']
            ],
            'notes' => '電車遅延のため修正申請します',
        ];

        $response = $this->actingAs($user)->post('/stamp_correction_request/from_detail', $requestData);

        $response->assertRedirect('/stamp_correction_request/list');
        $this->assertDatabaseHas('stamp_correction_requests', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function test_stamp_correction_request_validation_clock_in_after_clock_out()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $requestData = [
            'attendance_id' => $attendance->id,
            'clock_in' => '19:00', // 退勤時刻より後
            'clock_out' => '18:00',
            'notes' => '修正申請',
        ];

        $response = $this->actingAs($user)->post('/stamp_correction_request/from_detail', $requestData);

        $response->assertSessionHasErrors(['clock_in' => '出勤時間が不適切な値です']);
    }

    /** @test */
    public function test_stamp_correction_request_validation_break_time_after_clock_out()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $requestData = [
            'attendance_id' => $attendance->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_times' => [
                ['start_time' => '19:00', 'end_time' => '20:00'] // 退勤時刻より後
            ],
            'notes' => '修正申請',
        ];

        $response = $this->actingAs($user)->post('/stamp_correction_request/from_detail', $requestData);

        $response->assertSessionHasErrors(['break_times.0.start_time' => '休憩時間が不適切な値です']);
    }

    /** @test */
    public function test_stamp_correction_request_validation_notes_required()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $requestData = [
            'attendance_id' => $attendance->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'notes' => '', // 空の備考
        ];

        $response = $this->actingAs($user)->post('/stamp_correction_request/from_detail', $requestData);

        $response->assertSessionHasErrors(['notes' => '備考を記入してください']);
    }

    /** @test */
    public function test_user_can_filter_requests_by_status()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        
        // 承認待ち申請
        StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'pending',
        ]);
        
        // 承認済み申請
        StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'approved',
        ]);

        // 承認待ちタブ
        $response = $this->actingAs($user)->get('/stamp_correction_request/list?status=pending');
        $response->assertStatus(200);

        // 承認済みタブ
        $response = $this->actingAs($user)->get('/stamp_correction_request/list?status=approved');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_user_can_view_attendance_detail_with_pending_status()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}?request_status=pending");

        $response->assertStatus(200);
        $response->assertViewIs('attendance.detail');
    }

    /** @test */
    public function test_user_can_view_attendance_detail_with_approved_status()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}?request_status=approved");

        $response->assertStatus(200);
        $response->assertViewIs('attendance.detail');
    }

    /** @test */
    public function test_user_cannot_submit_request_for_other_users_attendance()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $otherUser->id]);

        $requestData = [
            'attendance_id' => $attendance->id,
            'clock_in' => '09:00',
            'notes' => '修正申請',
        ];

        $response = $this->actingAs($user)->post('/stamp_correction_request/from_detail', $requestData);

        $response->assertStatus(403);
    }
}
