<?php

namespace Database\Factories;

use App\Models\StampCorrectionRequest;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StampCorrectionRequest>
 */
class StampCorrectionRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $requestType = $this->faker->randomElement(['clock_in', 'clock_out', 'break_start', 'break_end', 'notes']);
        
        return [
            'user_id' => User::factory(),
            'attendance_id' => Attendance::factory(),
            'request_type' => $requestType,
            'requested_time' => $requestType !== 'notes' ? Carbon::now()->setTime(
                $this->faker->numberBetween(8, 18),
                $this->faker->randomElement([0, 15, 30, 45])
            ) : null,
            'reason' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'admin_comment' => $this->faker->optional()->sentence(),
            'approved_by' => null,
        ];
    }

    /**
     * 承認待ちの申請
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'admin_comment' => null,
            'approved_by' => null,
        ]);
    }

    /**
     * 承認済みの申請
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_by' => User::factory()->create(['is_admin' => true])->id,
            'admin_comment' => $this->faker->sentence(),
        ]);
    }

    /**
     * 却下された申請
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'approved_by' => User::factory()->create(['is_admin' => true])->id,
            'admin_comment' => $this->faker->sentence(),
        ]);
    }

    /**
     * 出勤時刻修正申請
     */
    public function clockIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'request_type' => 'clock_in',
            'requested_time' => Carbon::now()->setTime(
                $this->faker->numberBetween(8, 10),
                $this->faker->randomElement([0, 15, 30, 45])
            ),
        ]);
    }

    /**
     * 退勤時刻修正申請
     */
    public function clockOut(): static
    {
        return $this->state(fn (array $attributes) => [
            'request_type' => 'clock_out',
            'requested_time' => Carbon::now()->setTime(
                $this->faker->numberBetween(17, 20),
                $this->faker->randomElement([0, 15, 30, 45])
            ),
        ]);
    }

    /**
     * 休憩開始時刻修正申請
     */
    public function breakStart(): static
    {
        return $this->state(fn (array $attributes) => [
            'request_type' => 'break_start',
            'requested_time' => Carbon::now()->setTime(
                $this->faker->numberBetween(11, 13),
                $this->faker->randomElement([0, 15, 30, 45])
            ),
        ]);
    }

    /**
     * 休憩終了時刻修正申請
     */
    public function breakEnd(): static
    {
        return $this->state(fn (array $attributes) => [
            'request_type' => 'break_end',
            'requested_time' => Carbon::now()->setTime(
                $this->faker->numberBetween(12, 14),
                $this->faker->randomElement([0, 15, 30, 45])
            ),
        ]);
    }

    /**
     * 備考修正申請
     */
    public function notes(): static
    {
        return $this->state(fn (array $attributes) => [
            'request_type' => 'notes',
            'requested_time' => null,
            'reason' => $this->faker->paragraph(),
        ]);
    }
}
