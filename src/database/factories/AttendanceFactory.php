<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $workDate = $this->faker->dateTimeBetween('-30 days', 'now');
        $clockIn = Carbon::parse($workDate)->setTime(
            $this->faker->numberBetween(8, 10),
            $this->faker->randomElement([0, 15, 30, 45])
        );
        $clockOut = (clone $clockIn)->addHours($this->faker->numberBetween(7, 10));

        return [
            'user_id' => User::factory(),
            'work_date' => $workDate,
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'break_start' => $clockIn->copy()->addHours(3)->addMinutes($this->faker->numberBetween(0, 60)),
            'break_end' => $clockIn->copy()->addHours(4),
            'total_work_time' => $this->faker->numberBetween(420, 540), // 7-9時間（分）
            'total_break_time' => $this->faker->numberBetween(30, 90), // 30-90分
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * 今日の勤怠レコード
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'work_date' => Carbon::today(),
        ]);
    }

    /**
     * 出勤のみ（退勤なし）
     */
    public function clockedIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'clock_out' => null,
            'break_start' => null,
            'break_end' => null,
            'total_work_time' => null,
            'total_break_time' => null,
        ]);
    }

    /**
     * 休憩中
     */
    public function onBreak(): static
    {
        $clockIn = Carbon::now()->subHours(3);
        $breakStart = Carbon::now()->subMinutes(30);
        
        return $this->state(fn (array $attributes) => [
            'work_date' => Carbon::today(),
            'clock_in' => $clockIn,
            'clock_out' => null,
            'break_start' => $breakStart,
            'break_end' => null,
            'total_work_time' => null,
            'total_break_time' => null,
        ]);
    }

    /**
     * 完了した勤怠（出勤・退勤・休憩すべて完了）
     */
    public function completed(): static
    {
        $workDate = Carbon::today();
        $clockIn = $workDate->copy()->setTime(9, 0);
        $clockOut = $workDate->copy()->setTime(18, 0);
        $breakStart = $workDate->copy()->setTime(12, 0);
        $breakEnd = $workDate->copy()->setTime(13, 0);

        return $this->state(fn (array $attributes) => [
            'work_date' => $workDate,
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'break_start' => $breakStart,
            'break_end' => $breakEnd,
            'total_work_time' => 480, // 8時間
            'total_break_time' => 60, // 1時間
        ]);
    }
}
