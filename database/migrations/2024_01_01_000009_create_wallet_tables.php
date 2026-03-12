<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->integer('balance')->default(0); // in coins (1 coin = 1 UAH)
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', [
                'deposit',          // card top-up
                'withdrawal',       // cash withdrawal
                'transfer_out',     // transfer to another user
                'transfer_in',      // received from another user
                'commission',       // platform commission
                'reward',           // earned coins (homework, test, etc.)
                'penalty',          // deductions
                'purchase',         // shop purchase
                'bonus_purchase',   // hint, freeze, etc.
                'course_payment',   // course fee
                'vip_purchase',     // VIP status
                'resume_purchase',  // resume placement
                'material_purchase',// additional materials
                'donation',         // donation to academy
                'refund',           // refund
            ]);
            $table->integer('amount'); // positive = credit, negative = debit
            $table->integer('commission_amount')->default(0);
            $table->string('description')->nullable();
            $table->string('reference_type')->nullable(); // morphable type
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('related_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'completed', 'rejected'])->default('completed');
            $table->text('admin_note')->nullable(); // for withdrawal approvals
            $table->string('liqpay_order_id')->nullable();
            $table->string('liqpay_status')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'type']);
            $table->index(['reference_type', 'reference_id']);
        });

        // Withdrawal requests
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('amount'); // must be >= 100 and multiple of 100
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('pickup_note')->nullable(); // where to pick up cash
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('wallets');
    }
};
