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
        Schema::table('stamp_correction_requests', function (Blueprint $table) {
            // request_typeのenumにbreak_timesとmultiple_changesを追加
            $table->enum('request_type', ['clock_in', 'clock_out', 'break_start', 'break_end', 'break_times', 'multiple_changes'])->change();
            
            // original_timeとrequested_timeをtext型に変更（JSON文字列を保存するため）
            $table->text('original_time')->nullable()->change();
            $table->text('requested_time')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stamp_correction_requests', function (Blueprint $table) {
            // request_typeのenumからbreak_timesとmultiple_changesを削除
            $table->enum('request_type', ['clock_in', 'clock_out', 'break_start', 'break_end'])->change();
            
            // original_timeとrequested_timeをtime型に戻す
            $table->time('original_time')->nullable()->change();
            $table->time('requested_time')->change();
        });
    }
};
