<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class StampCorrectionRequestModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_stamp_correction_request_belongs_to_user()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        $request = StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
        ]);

        $this->assertInstanceOf(User::class, $request->user);
        $this->assertEquals($user->id, $request->user->id);
    }

    /** @test */
    public function test_stamp_correction_request_belongs_to_attendance()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        $request = StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
        ]);

        $this->assertInstanceOf(Attendance::class, $request->attendance);
        $this->assertEquals($attendance->id, $request->attendance->id);
    }

    /** @test */
    public function test_stamp_correction_request_belongs_to_approver()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        $request = StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'approved_by' => $admin->id,
        ]);

        $this->assertInstanceOf(User::class, $request->approver);
        $this->assertEquals($admin->id, $request->approver->id);
    }

    /** @test */
    public function test_status_label_accessor()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        
        $pendingRequest = StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'pending',
        ]);

        $approvedRequest = StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'approved',
        ]);

        $rejectedRequest = StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'rejected',
        ]);

        $this->assertEquals('承認待ち', $pendingRequest->statusLabel);
        $this->assertEquals('承認済み', $approvedRequest->statusLabel);
        $this->assertEquals('却下', $rejectedRequest->statusLabel);
    }

    /** @test */
    public function test_request_type_label_accessor()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        
        $clockInRequest = StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'request_type' => 'clock_in',
        ]);

        $clockOutRequest = StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'request_type' => 'clock_out',
        ]);

        $breakStartRequest = StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'request_type' => 'break_start',
        ]);

        $breakEndRequest = StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'request_type' => 'break_end',
        ]);

        $notesRequest = StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'request_type' => 'notes',
        ]);

        $this->assertEquals('出勤', $clockInRequest->requestTypeLabel);
        $this->assertEquals('退勤', $clockOutRequest->requestTypeLabel);
        $this->assertEquals('休憩開始', $breakStartRequest->requestTypeLabel);
        $this->assertEquals('休憩終了', $breakEndRequest->requestTypeLabel);
        $this->assertEquals('備考', $notesRequest->requestTypeLabel);
    }

    /** @test */
    public function test_status_class_accessor()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        
        $pendingRequest = StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'pending',
        ]);

        $approvedRequest = StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'approved',
        ]);

        $rejectedRequest = StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'rejected',
        ]);

        $this->assertEquals('text-orange-600', $pendingRequest->statusClass);
        $this->assertEquals('text-green-600', $approvedRequest->statusClass);
        $this->assertEquals('text-red-600', $rejectedRequest->statusClass);
    }

    /** @test */
    public function test_requested_time_cast_to_datetime()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        $request = StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_time' => '09:30:00',
        ]);

        $this->assertInstanceOf(Carbon::class, $request->requested_time);
        $this->assertEquals('09:30', $request->requested_time->format('H:i'));
    }

    /** @test */
    public function test_created_at_and_updated_at_cast_to_datetime()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        $request = StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
        ]);

        $this->assertInstanceOf(Carbon::class, $request->created_at);
        $this->assertInstanceOf(Carbon::class, $request->updated_at);
    }

    /** @test */
    public function test_fillable_attributes()
    {
        $fillable = [
            'user_id',
            'attendance_id',
            'request_type',
            'requested_time',
            'reason',
            'status',
            'admin_comment',
            'approved_by',
        ];

        $request = new StampCorrectionRequest();
        
        $this->assertEquals($fillable, $request->getFillable());
    }

    /** @test */
    public function test_default_status_is_pending()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        
        $request = StampCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'request_type' => 'clock_in',
            'requested_time' => Carbon::now()->setTime(9, 0),
            'reason' => 'テスト申請',
        ]);

        $this->assertEquals('pending', $request->status);
    }
}
