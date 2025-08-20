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
            $table->string('order_number', 50)->unique(); // AUTO_2024_001
            $table->foreignId('customer_id')->constrained()->onDelete('restrict');
            $table->foreignId('payment_method_id')->constrained()->onDelete('restrict');
            $table->enum('status', ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending');
            $table->decimal('subtotal', 12, 2); // Tổng tiền hàng trước giảm giá
            $table->decimal('customer_discount_amount', 12, 2)->default(0); // Giảm giá từ loại khách hàng
            $table->decimal('discount_amount', 12, 2)->default(0); // Tổng giảm giá từ các mã
            $table->decimal('total', 12, 2); // Tổng cuối cùng
            $table->text('notes')->nullable();
            $table->text('shipping_address')->nullable();
            $table->datetime('order_date');
            $table->datetime('shipped_date')->nullable();
            $table->datetime('delivered_date')->nullable();
            $table->timestamps();
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
