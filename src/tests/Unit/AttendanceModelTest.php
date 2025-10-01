<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_attendance_belongs_to_user()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $attendance->user);
        $this->assertEquals($user->id, $attendance->user->id);
    }

    /** @test */
    public function test_attendance_has_many_break_times()
    {
        $attendance = Attendance::factory()->create();
        BreakTime::factory()->count(3)->create(['attendance_id' => $attendance->id]);

        $this->assertCount(3, $attendance->breakTimes);
        $this->assertInstanceOf(BreakTime::class, $attendance->breakTimes->first());
    }

    /** @test */
    public function test_calculate_work_time_without_breaks()
    {
        $attendance = Attendance::factory()->create([
            'clock_in' => Carbon::now()->setTime(9, 0),
            'clock_out' => Carbon::now()->setTime(17, 0),
        ]);

        $workTime = $attendance->calculateWorkTime();
        
        $this->assertEquals(480, $workTime); // 8時間 = 480分
    }

    /** @test */
    public function test_calculate_work_time_with_breaks()
    {
        $attendance = Attendance::factory()->create([
            'clock_in' => Carbon::now()->setTime(9, 0),
            'clock_out' => Carbon::now()->setTime(18, 0),
        ]);

        // 1時間の休憩を追加
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::now()->setTime(12, 0),
            'end_time' => Carbon::now()->setTime(13, 0),
            'order' => 1,
        ]);

        $workTime = $attendance->calculateWorkTime();
        
        $this->assertEquals(480, $workTime); // 9時間 - 1時間休憩 = 8時間 = 480分
    }

    /** @test */
    public function test_calculate_work_time_with_multiple_breaks()
    {
        $attendance = Attendance::factory()->create([
            'clock_in' => Carbon::now()->setTime(9, 0),
            'clock_out' => Carbon::now()->setTime(18, 0),
        ]);

        // 複数の休憩を追加
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::now()->setTime(10, 30),
            'end_time' => Carbon::now()->setTime(10, 45),
            'order' => 1,
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::now()->setTime(12, 0),
            'end_time' => Carbon::now()->setTime(13, 0),
            'order' => 2,
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::now()->setTime(15, 0),
            'end_time' => Carbon::now()->setTime(15, 15),
            'order' => 3,
        ]);

        $workTime = $attendance->calculateWorkTime();
        
        // 9時間 - (15分 + 60分 + 15分) = 9時間 - 1.5時間 = 7.5時間 = 450分
        $this->assertEquals(450, $workTime);
    }

    /** @test */
    public function test_calculate_break_time()
    {
        $attendance = Attendance::factory()->create();

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::now()->setTime(12, 0),
            'end_time' => Carbon::now()->setTime(13, 0),
            'order' => 1,
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::now()->setTime(15, 0),
            'end_time' => Carbon::now()->setTime(15, 30),
            'order' => 2,
        ]);

        $breakTime = $attendance->calculateBreakTime();
        
        $this->assertEquals(90, $breakTime); // 60分 + 30分 = 90分
    }

    /** @test */
    public function test_calculate_break_time_with_ongoing_break()
    {
        $attendance = Attendance::factory()->create();

        // 完了した休憩
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::now()->setTime(12, 0),
            'end_time' => Carbon::now()->setTime(13, 0),
            'order' => 1,
        ]);

        // 進行中の休憩（end_timeがnull）
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::now()->setTime(15, 0),
            'end_time' => null,
            'order' => 2,
        ]);

        $breakTime = $attendance->calculateBreakTime();
        
        $this->assertEquals(60, $breakTime); // 完了した休憩のみ計算: 60分
    }

    /** @test */
    public function test_formatted_work_time_attribute()
    {
        $attendance = Attendance::factory()->create([
            'total_work_time' => 510, // 8時間30分
        ]);

        $formatted = $attendance->getFormattedWorkTimeAttribute();
        
        $this->assertEquals('8時間30分', $formatted);
    }

    /** @test */
    public function test_formatted_break_time_attribute()
    {
        $attendance = Attendance::factory()->create([
            'total_break_time' => 90, // 1時間30分
        ]);

        $formatted = $attendance->getFormattedBreakTimeAttribute();
        
        $this->assertEquals('1時間30分', $formatted);
    }

    /** @test */
    public function test_formatted_time_with_zero_minutes()
    {
        $attendance = Attendance::factory()->create([
            'total_work_time' => 480, // 8時間00分
        ]);

        $formatted = $attendance->getFormattedWorkTimeAttribute();
        
        $this->assertEquals('8時間0分', $formatted);
    }

    /** @test */
    public function test_formatted_time_with_null_value()
    {
        $attendance = Attendance::factory()->create([
            'total_work_time' => null,
        ]);

        $formatted = $attendance->getFormattedWorkTimeAttribute();
        
        $this->assertEquals('--:--', $formatted);
    }

    /** @test */
    public function test_work_date_cast_to_date()
    {
        $attendance = Attendance::factory()->create([
            'work_date' => '2025-09-27',
        ]);

        $this->assertInstanceOf(Carbon::class, $attendance->work_date);
        $this->assertEquals('2025-09-27', $attendance->work_date->format('Y-m-d'));
    }

    /** @test */
    public function test_clock_times_cast_to_datetime()
    {
        $attendance = Attendance::factory()->create([
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $this->assertInstanceOf(Carbon::class, $attendance->clock_in);
        $this->assertInstanceOf(Carbon::class, $attendance->clock_out);
        $this->assertEquals('09:00', $attendance->clock_in->format('H:i'));
        $this->assertEquals('18:00', $attendance->clock_out->format('H:i'));
    }
}
