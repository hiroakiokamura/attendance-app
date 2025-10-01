<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'break_start',
        'break_end',
        'total_work_time',
        'total_break_time',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'clock_in' => 'datetime',
            'clock_out' => 'datetime',
            'break_start' => 'datetime',
            'break_end' => 'datetime',
        ];
    }

    /**
     * この勤怠記録の所有者
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * この勤怠記録への打刻修正申請
     */
    public function stampCorrectionRequests()
    {
        return $this->hasMany(StampCorrectionRequest::class);
    }

    /**
     * この勤怠記録の休憩時間
     */
    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class)->orderBy('order');
    }

    /**
     * 勤務時間を計算（分単位）
     */
    public function calculateWorkTime(): int
    {
        if (!$this->clock_in || !$this->clock_out) {
            return 0;
        }

        $totalMinutes = $this->clock_in->diffInMinutes($this->clock_out);
        $breakMinutes = $this->total_break_time ?? 0;

        return max(0, $totalMinutes - $breakMinutes);
    }

    /**
     * 休憩時間を計算（分単位） - BreakTimeレコードから計算
     */
    public function calculateBreakTime(): int
    {
        $totalBreakMinutes = 0;
        
        foreach ($this->breakTimes as $breakTime) {
            if ($breakTime->start_time && $breakTime->end_time) {
                $totalBreakMinutes += $breakTime->start_time->diffInMinutes($breakTime->end_time);
            }
        }
        
        return $totalBreakMinutes;
    }

    /**
     * 勤務時間を時:分形式で取得
     */
    public function getFormattedWorkTimeAttribute(): string
    {
        if ($this->total_work_time === null) {
            return '--:--';
        }
        
        $minutes = $this->total_work_time;
        $hours = intval($minutes / 60);
        $mins = $minutes % 60;
        return sprintf('%d時間%d分', $hours, $mins);
    }

    /**
     * 休憩時間を時:分形式で取得
     */
    public function getFormattedBreakTimeAttribute(): string
    {
        if ($this->total_break_time === null) {
            return '--:--';
        }
        
        $minutes = $this->total_break_time;
        $hours = intval($minutes / 60);
        $mins = $minutes % 60;
        return sprintf('%d時間%d分', $hours, $mins);
    }
}
