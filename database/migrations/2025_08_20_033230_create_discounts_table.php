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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique(); // Mã giảm giá: SAVE10, WELCOME20
            $table->string('name', 255); // Tên chương trình
            $table->text('description')->nullable();
            $table->enum('type', ['percentage', 'fixed_amount']); // % hoặc số tiền cố định
            $table->decimal('value', 12, 2); // Giá trị giảm (% hoặc số tiền)
            $table->decimal('min_order_amount', 12, 2)->default(0); // Số tiền tối thiểu
            $table->decimal('max_discount_amount', 12, 2)->nullable(); // Giới hạn số tiền giảm tối đa
            $table->integer('usage_limit')->nullable(); // Số lần sử dụng tối đa
            $table->integer('used_count')->default(0); // Đã sử dụng bao nhiêu lần
            $table->boolean('can_stack')->default(false); // Có thể chồng với discount khác không
            $table->datetime('start_date');
            $table->datetime('end_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
