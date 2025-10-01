<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // 日本時間に設定
        config(['app.timezone' => 'Asia/Tokyo']);
        Carbon::setTestNow(Carbon::parse('2025-09-27 10:00:00', 'Asia/Tokyo'));
    }

    /** @test */
    public function test_attendance_index_screen_can_be_rendered()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertViewIs('attendance.index');
        $response->assertViewHas('attendance');
    }

    /** @test */
    public function test_user_can_clock_in()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/attendance/clock-in');

        $response->assertRedirect('/attendance');
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => Carbon::today()->format('Y-m-d'),
        ]);
    }

    /** @test */
    public function test_user_can_clock_out()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(8),
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)->post('/attendance/clock-out');

        $response->assertRedirect('/attendance');
        $attendance->refresh();
        $this->assertNotNull($attendance->clock_out);
    }

    /** @test */
    public function test_user_can_start_break()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(2),
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)->post('/attendance/break-start');

        $response->assertRedirect('/attendance');
        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
            'end_time' => null, // 休憩中
        ]);
    }

    /** @test */
    public function test_user_can_end_break()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(2),
            'clock_out' => null,
        ]);
        
        $breakTime = BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::now()->subMinutes(30),
            'end_time' => null,
            'order' => 1,
        ]);

        $response = $this->actingAs($user)->post('/attendance/break-end');

        $response->assertRedirect('/attendance');
        $breakTime->refresh();
        $this->assertNotNull($breakTime->end_time);
    }

    /** @test */
    public function test_user_can_view_attendance_list()
    {
        $user = User::factory()->create();
        Attendance::factory()->count(5)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertViewIs('attendance.list');
        $response->assertViewHas('attendances');
    }

    /** @test */
    public function test_user_can_view_attendance_detail()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertViewIs('attendance.detail');
        $response->assertViewHas('attendance', $attendance);
    }

    /** @test */
    public function test_user_cannot_view_other_users_attendance()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(404);
    }

    /** @test */
    public function test_user_can_only_clock_in_once_per_day()
    {
        $user = User::factory()->create();
        
        // 最初の出勤
        $this->actingAs($user)->post('/attendance/clock-in');
        
        // 2回目の出勤を試行
        $response = $this->actingAs($user)->post('/attendance/clock-in');

        $response->assertRedirect('/attendance');
        
        // 1日1レコードのみ作成されていることを確認
        $this->assertEquals(1, Attendance::where('user_id', $user->id)
            ->whereDate('work_date', Carbon::today())
            ->count());
    }

    /** @test */
    public function test_multiple_breaks_are_supported()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(4),
            'clock_out' => null,
        ]);

        // 1回目の休憩
        $this->actingAs($user)->post('/attendance/break-start');
        $this->actingAs($user)->post('/attendance/break-end');
        
        // 2回目の休憩
        $this->actingAs($user)->post('/attendance/break-start');
        $this->actingAs($user)->post('/attendance/break-end');

        $this->assertEquals(2, BreakTime::where('attendance_id', $attendance->id)->count());
    }

    /** @test */
    public function test_work_time_is_calculated_correctly()
    {
        $user = User::factory()->create();
        $clockIn = Carbon::now()->setTime(9, 0);
        $clockOut = Carbon::now()->setTime(18, 0);
        
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
        ]);

        // 1時間の休憩を追加
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::now()->setTime(12, 0),
            'end_time' => Carbon::now()->setTime(13, 0),
            'order' => 1,
        ]);

        $expectedWorkTime = 8 * 60; // 9時間 - 1時間休憩 = 8時間（分）
        $calculatedWorkTime = $attendance->calculateWorkTime();
        
        $this->assertEquals($expectedWorkTime, $calculatedWorkTime);
    }
}
