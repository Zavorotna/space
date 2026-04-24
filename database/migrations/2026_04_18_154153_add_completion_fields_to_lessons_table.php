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
        Schema::table('lessons', function (Blueprint $table) {
            $table->enum('completion_status', ['full', 'partial', 'cancelled', 'rescheduled'])
                ->nullable()->after('attendance_confirmed');
            $table->unsignedSmallInteger('actual_minutes')->nullable()->after('completion_status');
            $table->text('completion_note')->nullable()->after('actual_minutes');
            $table->timestamp('completion_noted_at')->nullable()->after('completion_note');
            $table->foreignId('makeup_for_lesson_id')->nullable()->after('completion_noted_at')
                ->constrained('lessons')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropForeign(['makeup_for_lesson_id']);
            $table->dropColumn(['completion_status', 'actual_minutes', 'completion_note', 'completion_noted_at', 'makeup_for_lesson_id']);
        });
    }
};
