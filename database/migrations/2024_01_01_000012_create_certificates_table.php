<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['bw', 'color', 'guaranteed']); // ЧБ / Кольоровий / З гарантією
            $table->decimal('success_rate', 5, 2)->default(0);
            $table->integer('discount_next_course')->default(0); // 0, 10, or 20 percent
            $table->boolean('discount_used')->default(false);
            $table->string('certificate_number')->unique()->nullable();
            // photo uploaded via Spatie Media Library
            $table->timestamps();
            $table->unique(['course_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
