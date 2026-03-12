<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('passing_score')->default(60); // percentage
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('test_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained()->cascadeOnDelete();
            $table->text('text');
            $table->enum('type', ['single', 'multiple'])->default('single');
            $table->text('hint')->nullable(); // purchasable hint
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('test_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('test_questions')->cascadeOnDelete();
            $table->text('text');
            $table->boolean('is_correct')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('test_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('attempt_number')->default(1);
            $table->integer('score')->default(0); // percentage
            $table->boolean('passed')->default(false);
            $table->integer('coins_awarded')->default(0);
            $table->integer('hints_used')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('test_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('test_attempts')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('test_questions')->cascadeOnDelete();
            $table->json('selected_options')->nullable(); // array of option IDs
            $table->boolean('is_correct')->default(false);
            $table->boolean('hint_used')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_attempt_answers');
        Schema::dropIfExists('test_attempts');
        Schema::dropIfExists('test_options');
        Schema::dropIfExists('test_questions');
        Schema::dropIfExists('tests');
    }
};
