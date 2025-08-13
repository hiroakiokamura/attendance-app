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
        Schema::create('stamp_correction_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('attendance_id')->constrained()->onDelete('cascade');
            $table->enum('request_type', ['clock_in', 'clock_out', 'break_start', 'break_end']); // 修正種別
            $table->time('original_time')->nullable(); // 元の時刻
            $table->time('requested_time'); // 修正希望時刻
            $table->text('reason'); // 修正理由
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // 承認状況
            $table->text('admin_comment')->nullable(); // 管理者コメント
            $table->timestamp('approved_at')->nullable(); // 承認日時
            $table->foreignId('approved_by')->nullable()->constrained('users'); // 承認者
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stamp_correction_requests');
    }
};
