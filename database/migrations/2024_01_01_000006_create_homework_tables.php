<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homework_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->integer('reward_coins')->default(15); // 5/15/25
            $table->date('deadline');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('homework_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homework_id')->constrained('homework_assignments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('links')->nullable(); // JSON array of links
            $table->enum('status', ['submitted', 'accepted', 'revision'])->default('submitted');
            $table->text('teacher_comment')->nullable();
            $table->integer('revision_count')->default(0);
            $table->boolean('early_submission')->default(false); // 2+ days before deadline
            $table->boolean('coins_awarded')->default(false);
            $table->integer('freeze_days_used')->default(0); // max 5
            $table->date('effective_deadline')->nullable(); // after freeze
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homework_submissions');
        Schema::dropIfExists('homework_assignments');
    }
};
