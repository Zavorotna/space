<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deletion_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->text('reason')->nullable();
            $table->string('deletable_type');
            $table->unsignedBigInteger('deletable_id');
            $table->index(['deletable_type', 'deletable_id']);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('platform_notifications', function (Blueprint $table) {
            $table->foreignId('deletion_request_id')
                ->nullable()
                ->constrained('deletion_requests')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('platform_notifications', function (Blueprint $table) {
            $table->dropForeign(['deletion_request_id']);
            $table->dropColumn('deletion_request_id');
        });
        Schema::dropIfExists('deletion_requests');
    }
};