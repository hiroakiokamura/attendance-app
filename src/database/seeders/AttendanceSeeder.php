<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 一般ユーザーを取得
        $users = User::where('is_admin', false)->get();

        if ($users->isEmpty()) {
            $this->command->info('一般ユーザーが見つかりません。先にAdminUserSeederを実行してください。');
            return;
        }

        // 今日から過去7日分の勤怠データを作成
        for ($i = 0; $i < 7; $i++) {
            $workDate = Carbon::now()->subDays($i);
            
            foreach ($users as $user) {
                // 80%の確率で勤怠データを作成（休日をシミュレート）
                if (rand(1, 100) <= 80) {
                    $clockIn = $workDate->copy()->setTime(9, 0)->addMinutes(rand(-30, 30));
                    $clockOut = $workDate->copy()->setTime(18, 0)->addMinutes(rand(-30, 30));
                    $breakStart = $workDate->copy()->setTime(12, 0)->addMinutes(rand(-15, 15));
                    $breakEnd = $workDate->copy()->setTime(13, 0)->addMinutes(rand(-15, 15));
                    
                    $totalBreakTime = $breakStart->diffInMinutes($breakEnd);
                    $totalWorkTime = $clockIn->diffInMinutes($clockOut) - $totalBreakTime;

                    Attendance::create([
                        'user_id' => $user->id,
                        'work_date' => $workDate->format('Y-m-d'),
                        'clock_in' => $clockIn,
                        'clock_out' => $clockOut,
                        'break_start' => $breakStart,
                        'break_end' => $breakEnd,
                        'total_work_time' => $totalWorkTime,
                        'total_break_time' => $totalBreakTime,
                        'notes' => '通常勤務',
                    ]);
                }
            }
        }

        $this->command->info('勤怠データを作成しました。');
    }
}
