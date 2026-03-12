<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('price_coins')->default(0);
            $table->decimal('price_uah', 10, 2)->default(0);
            $table->boolean('accept_coins')->default(true);
            $table->boolean('accept_card')->default(true);
            $table->integer('stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('shop_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('shop_products')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->enum('payment_method', ['coins', 'card'])->default('coins');
            $table->integer('total_coins')->default(0);
            $table->decimal('total_uah', 10, 2)->default(0);
            $table->enum('status', ['pending', 'paid', 'shipped', 'completed', 'cancelled'])->default('pending');
            $table->string('liqpay_order_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_orders');
        Schema::dropIfExists('shop_products');
    }
};
