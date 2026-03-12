<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Purchased bonuses stored in inventory
        Schema::create('bonus_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['test_hint', 'homework_freeze', 'graduation_freeze']);
            $table->integer('quantity')->default(1);
            $table->integer('used')->default(0);
            $table->boolean('sellable')->default(false); // can sell after course at -10%
            $table->timestamps();
        });

        Schema::create('bonus_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('bonus_inventory')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('used_on_type')->nullable(); // test_question, homework, graduation
            $table->unsignedBigInteger('used_on_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bonus_usages');
        Schema::dropIfExists('bonus_inventory');
    }
};
