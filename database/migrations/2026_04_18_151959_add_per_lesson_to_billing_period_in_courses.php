<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->enum('billing_period', ['one_time', 'monthly', 'per_lesson'])
                ->default('monthly')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->enum('billing_period', ['one_time', 'monthly'])
                ->default('monthly')
                ->change();
        });
    }
};