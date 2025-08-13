<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('work_date'); // 勤務日
            $table->time('clock_in')->nullable(); // 出勤時刻
            $table->time('clock_out')->nullable(); // 退勤時刻
            $table->time('break_start')->nullable(); // 休憩開始時刻
            $table->time('break_end')->nullable(); // 休憩終了時刻
            $table->integer('total_work_time')->nullable(); // 総勤務時間（分）
            $table->integer('total_break_time')->nullable(); // 総休憩時間（分）
            $table->text('notes')->nullable(); // 備考
            $table->timestamps();

            // ユニーク制約: 一人のユーザーは1日に1つの勤怠記録のみ
            $table->unique(['user_id', 'work_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
