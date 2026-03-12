<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->unique()->nullable();
            $table->string('email')->unique()->nullable();
            $table->date('birthday')->nullable();
            $table->string('password')->nullable();
            $table->string('google_id')->nullable();
            $table->enum('role', ['superadmin', 'admin', 'teacher', 'student', 'parent', 'registered', 'guest'])->default('registered');
            $table->boolean('is_vip')->default(false);
            $table->timestamp('vip_expires_at')->nullable();
            $table->boolean('is_trusted_teacher')->default(false); // extended hours
            $table->integer('login_streak')->default(0);
            $table->date('last_login_date')->nullable();
            $table->integer('longest_streak')->default(0);
            $table->text('bio')->nullable();
            $table->string('avatar')->nullable(); // main avatar
            $table->json('extra_avatars')->nullable(); // VIP: up to 5
            $table->boolean('resume_published')->default(false);
            $table->timestamp('resume_expires_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
