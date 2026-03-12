<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('reward_coins')->default(0);
            $table->string('icon')->nullable();
            $table->enum('type', ['login_streak', 'top_monthly', 'custom'])->default('custom');
            $table->integer('threshold')->nullable(); // e.g., 10 days for streak
            $table->timestamps();
        });

        Schema::create('user_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('achievement_id')->constrained()->cascadeOnDelete();
            $table->integer('coins_awarded')->default(0);
            $table->timestamp('earned_at');
            $table->timestamps();
            $table->unique(['user_id', 'achievement_id']);
        });

        // Monthly leaderboard snapshots
        Schema::create('monthly_leaderboards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('year');
            $table->integer('month');
            $table->decimal('score', 10, 2)->default(0);
            $table->integer('rank')->nullable();
            $table->integer('coins_awarded')->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_leaderboards');
        Schema::dropIfExists('user_achievements');
        Schema::dropIfExists('achievements');
    }
};
