<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('program')->nullable(); // course program/curriculum
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('price', 10, 2)->default(0);
            $table->enum('billing_period', ['one_time', 'monthly'])->default('monthly');
            $table->enum('status', ['waiting', 'enrolling', 'active', 'completed'])->default('waiting');
            $table->enum('type', ['group', 'individual'])->default('group');
            $table->date('intro_date')->nullable(); // дата відкритого заняття
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('telegram_link')->nullable();
            $table->string('liqpay_merchant_id')->nullable(); // per-course FOP account
            $table->string('liqpay_private_key')->nullable();
            $table->boolean('has_graduation_project')->default(true);
            $table->foreignId('template_id')->nullable()->constrained('courses')->nullOnDelete(); // copied from
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('course_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'approved', 'active', 'completed', 'dropped'])->default('pending');
            $table->boolean('is_paid')->default(false);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('active_until')->nullable(); // 1 year from paid course start for coin eligibility
            $table->decimal('success_rate', 5, 2)->default(0); // percentage
            $table->boolean('review_submitted')->default(false);
            $table->boolean('telegram_link_shown')->default(false);
            $table->timestamps();
            $table->unique(['course_id', 'user_id']);
        });

        Schema::create('course_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('note')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('course_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('rating'); // 1-5
            $table->text('text')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->timestamps();
            $table->unique(['course_id', 'user_id']);
        });

        // Referrals for group courses
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referred_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->boolean('rewarded')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('course_reviews');
        Schema::dropIfExists('course_applications');
        Schema::dropIfExists('course_user');
        Schema::dropIfExists('courses');
    }
};
