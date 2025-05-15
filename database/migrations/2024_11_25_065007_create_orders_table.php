<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('order_number')->unique();
            $table->integer('subtotal')->default(0);
            $table->integer('total_amount');
            $table->enum('status', ['checking', 'pending', 'processing', 'shipped', 'completed', 'cancelled'])->default('checking');
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid');
            
            // Shipping Information
            $table->string('recipient_name')->nullable();
            $table->string('phone')->nullable();
            $table->integer('shipping_cost')->default(0);
            $table->text('shipping_address')->nullable();
         
        

            // Payment Gateway
            $table->string('payment_gateway_transaction_id')->nullable();
            $table->longtext('payment_gateway_data')->nullable();

            $table->text('noted')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
