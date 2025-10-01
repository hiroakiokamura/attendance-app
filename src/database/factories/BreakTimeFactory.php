<?php

namespace Database\Factories;

use App\Models\BreakTime;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BreakTime>
 */
class BreakTimeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = Carbon::now()->setTime(
            $this->faker->numberBetween(10, 15),
            $this->faker->randomElement([0, 15, 30, 45])
        );
        $endTime = $startTime->copy()->addMinutes($this->faker->numberBetween(15, 90));

        return [
            'attendance_id' => Attendance::factory(),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'order' => $this->faker->numberBetween(1, 3),
        ];
    }

    /**
     * 進行中の休憩（end_timeがnull）
     */
    public function ongoing(): static
    {
        return $this->state(fn (array $attributes) => [
            'end_time' => null,
        ]);
    }

    /**
     * 昼休憩
     */
    public function lunchBreak(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => Carbon::now()->setTime(12, 0),
            'end_time' => Carbon::now()->setTime(13, 0),
            'order' => 1,
        ]);
    }

    /**
     * 短い休憩
     */
    public function shortBreak(): static
    {
        $startTime = Carbon::now()->setTime(15, 0);
        return $this->state(fn (array $attributes) => [
            'start_time' => $startTime,
            'end_time' => $startTime->copy()->addMinutes(15),
            'order' => 2,
        ]);
    }
}
