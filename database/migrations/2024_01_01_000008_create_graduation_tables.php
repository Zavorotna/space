<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('graduation_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('deadline');
            $table->timestamps();
        });

        Schema::create('graduation_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('graduation_projects')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('links')->nullable();
            $table->enum('status', ['submitted', 'accepted', 'revision', 'commission'])->default('submitted');
            $table->text('teacher_comment')->nullable();
            $table->integer('revision_count')->default(0);
            $table->integer('coins_awarded')->default(0); // starts at 100, -5 per revision, min 25
            $table->boolean('is_defended')->default(false);
            $table->integer('freeze_days_used')->default(0); // max 20
            $table->date('effective_deadline')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('graduation_submissions');
        Schema::dropIfExists('graduation_projects');
    }
};
