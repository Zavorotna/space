<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_applications', function (Blueprint $table) {
            // Track which teacher notification was sent so we can dismiss it
            $table->unsignedBigInteger('notification_id')->nullable()->after('processed_by');
        });
    }

    public function down(): void
    {
        Schema::table('course_applications', function (Blueprint $table) {
            $table->dropColumn('notification_id');
        });
    }
};