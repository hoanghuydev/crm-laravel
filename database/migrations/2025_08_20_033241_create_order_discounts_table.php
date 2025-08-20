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
        Schema::create('order_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('discount_id')->constrained()->onDelete('restrict');
            $table->decimal('discount_amount', 12, 2); // Số tiền thực tế đã giảm
            $table->timestamps();
            
            // Một đơn hàng có thể áp dụng cùng mã giảm giá nhiều lần không
            $table->unique(['order_id', 'discount_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_discounts');
    }
};
