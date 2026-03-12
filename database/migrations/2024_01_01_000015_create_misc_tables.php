<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Notes
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // author
            $table->foreignId('recipient_id')->nullable()->constrained('users')->cascadeOnDelete(); // null = personal
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->text('content');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        // Internal notifications
        Schema::create('platform_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // lesson_reminder, birthday, absence, remark, etc.
            $table->string('title');
            $table->text('message')->nullable();
            $table->string('link')->nullable();
            $table->boolean('is_read')->default(false);
            $table->boolean('push_sent')->default(false);
            $table->timestamps();
            $table->index(['user_id', 'is_read']);
        });

        // Push subscription for browser notifications
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('endpoint');
            $table->string('p256dh')->nullable();
            $table->string('auth')->nullable();
            $table->timestamps();
        });

        // Additional course materials
        Schema::create('additional_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->integer('price_coins')->default(0); // 0 = free
            $table->timestamps();
        });

        Schema::create('material_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('additional_materials')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('price_paid')->default(0);
            $table->timestamps();
            $table->unique(['material_id', 'user_id']);
        });

        // Birthday rewards tracking
        Schema::create('birthday_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('year');
            $table->integer('coins_awarded')->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'course_id', 'year']);
        });

        // Donation tracking
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('amount');
            $table->integer('total_donated')->default(0); // running total for VIP check
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
        Schema::dropIfExists('birthday_rewards');
        Schema::dropIfExists('material_purchases');
        Schema::dropIfExists('additional_materials');
        Schema::dropIfExists('push_subscriptions');
        Schema::dropIfExists('platform_notifications');
        Schema::dropIfExists('notes');
    }
};
