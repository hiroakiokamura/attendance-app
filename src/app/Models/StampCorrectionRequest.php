<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StampCorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'request_type',
        'original_time',
        'requested_time',
        'reason',
        'status',
        'admin_comment',
        'approved_at',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'original_time' => 'datetime',
            'requested_time' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    /**
     * 申請者
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 対象の勤怠記録
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * 承認者
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * ステータスの日本語ラベル
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => '承認待ち',
            'approved' => '承認済み',
            'rejected' => '却下',
            default => '不明',
        };
    }

    /**
     * 修正種別の日本語ラベル
     */
    public function getRequestTypeLabelAttribute(): string
    {
        return match($this->request_type) {
            'clock_in' => '出勤時刻',
            'clock_out' => '退勤時刻',
            'break_start' => '休憩開始時刻',
            'break_end' => '休憩終了時刻',
            default => '不明',
        };
    }

    /**
     * ステータスのCSSクラス
     */
    public function getStatusClassAttribute(): string
    {
        return match($this->status) {
            'pending' => 'status-pending bg-yellow-100 text-yellow-800',
            'approved' => 'status-approved bg-green-100 text-green-800',
            'rejected' => 'status-rejected bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
