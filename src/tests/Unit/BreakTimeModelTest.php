<?php

namespace Tests\Unit;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class BreakTimeModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_break_time_belongs_to_attendance()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        $breakTime = BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::now()->setTime(12, 0),
            'end_time' => Carbon::now()->setTime(13, 0),
            'order' => 1,
        ]);

        $this->assertInstanceOf(Attendance::class, $breakTime->attendance);
        $this->assertEquals($attendance->id, $breakTime->attendance->id);
    }

    /** @test */
    public function test_start_time_and_end_time_cast_to_datetime()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        $breakTime = BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
            'order' => 1,
        ]);

        $this->assertInstanceOf(Carbon::class, $breakTime->start_time);
        $this->assertInstanceOf(Carbon::class, $breakTime->end_time);
        $this->assertEquals('12:00', $breakTime->start_time->format('H:i'));
        $this->assertEquals('13:00', $breakTime->end_time->format('H:i'));
    }

    /** @test */
    public function test_end_time_can_be_null()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        $breakTime = BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::now()->setTime(12, 0),
            'end_time' => null, // 休憩中
            'order' => 1,
        ]);

        $this->assertInstanceOf(Carbon::class, $breakTime->start_time);
        $this->assertNull($breakTime->end_time);
    }

    /** @test */
    public function test_fillable_attributes()
    {
        $fillable = [
            'attendance_id',
            'start_time',
            'end_time',
            'order',
        ];

        $breakTime = new BreakTime();
        
        $this->assertEquals($fillable, $breakTime->getFillable());
    }

    /** @test */
    public function test_break_times_are_ordered_by_order_column()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        
        // 順序を逆にして作成
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::now()->setTime(15, 0),
            'end_time' => Carbon::now()->setTime(15, 15),
            'order' => 2,
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::now()->setTime(12, 0),
            'end_time' => Carbon::now()->setTime(13, 0),
            'order' => 1,
        ]);

        $breakTimes = $attendance->breakTimes;
        
        $this->assertEquals(1, $breakTimes->first()->order);
        $this->assertEquals(2, $breakTimes->last()->order);
    }

    /** @test */
    public function test_break_duration_calculation()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        
        $breakTime = BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::now()->setTime(12, 0),
            'end_time' => Carbon::now()->setTime(13, 30), // 1時間30分
            'order' => 1,
        ]);

        $duration = $breakTime->start_time->diffInMinutes($breakTime->end_time);
        
        $this->assertEquals(90, $duration); // 90分
    }

    /** @test */
    public function test_ongoing_break_has_no_duration()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        
        $breakTime = BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::now()->setTime(12, 0),
            'end_time' => null, // 進行中
            'order' => 1,
        ]);

        // end_timeがnullの場合、計算できない
        $this->assertNull($breakTime->end_time);
    }

    /** @test */
    public function test_multiple_breaks_for_same_attendance()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        
        // 複数の休憩を作成
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

        $this->assertCount(3, $attendance->breakTimes);
    }

    /** @test */
    public function test_break_time_deletion_cascade()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::now()->setTime(12, 0),
            'end_time' => Carbon::now()->setTime(13, 0),
            'order' => 1,
        ]);

        $this->assertCount(1, BreakTime::all());
        
        // 勤怠レコードを削除
        $attendance->delete();
        
        // BreakTimeも自動削除される（外部キー制約のcascade）
        $this->assertCount(0, BreakTime::all());
    }
}
