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
        Schema::create('customer_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique(); // VIP, Regular, Bronze, Silver, Gold
            $table->string('description')->nullable();
            $table->decimal('discount_percentage', 5, 2)->default(0); // % giảm giá cho loại khách hàng này
            $table->decimal('min_order_amount', 12, 2)->default(0); // Số tiền tối thiểu để áp dụng ưu đãi
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_types');
    }
};
