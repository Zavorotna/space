<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->json('schedule_days')->nullable()->after('end_date');        // [1,3,5] = Mon/Wed/Fri
            $table->time('schedule_start_time')->nullable()->after('schedule_days');
            $table->time('schedule_end_time')->nullable()->after('schedule_start_time');
            $table->enum('schedule_mode', ['online', 'offline'])->default('online')->after('schedule_end_time');
            $table->foreignId('schedule_location_id')->nullable()->constrained('locations')->nullOnDelete()->after('schedule_mode');
            $table->foreignId('schedule_classroom_id')->nullable()->constrained('classrooms')->nullOnDelete()->after('schedule_location_id');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['schedule_location_id']);
            $table->dropForeign(['schedule_classroom_id']);
            $table->dropColumn([
                'schedule_days', 'schedule_start_time', 'schedule_end_time',
                'schedule_mode', 'schedule_location_id', 'schedule_classroom_id',
            ]);
        });
    }
};