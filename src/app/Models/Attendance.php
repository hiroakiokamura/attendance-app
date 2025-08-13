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
     * 休憩時間を計算（分単位）
     */
    public function calculateBreakTime(): int
    {
        if (!$this->break_start || !$this->break_end) {
            return 0;
        }

        return $this->break_start->diffInMinutes($this->break_end);
    }

    /**
     * 勤務時間を時:分形式で取得
     */
    public function getFormattedWorkTimeAttribute(): string
    {
        $minutes = $this->calculateWorkTime();
        $hours = intval($minutes / 60);
        $mins = $minutes % 60;
        return sprintf('%02d:%02d', $hours, $mins);
    }

    /**
     * 休憩時間を時:分形式で取得
     */
    public function getFormattedBreakTimeAttribute(): string
    {
        $minutes = $this->total_break_time ?? 0;
        $hours = intval($minutes / 60);
        $mins = $minutes % 60;
        return sprintf('%02d:%02d', $hours, $mins);
    }
}
