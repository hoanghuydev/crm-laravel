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
        Schema::table('discounts', function (Blueprint $table) {
            $table->enum('discount_category', ['product', 'payment', 'customer', 'seasonal', 'promotion'])
                  ->default('product')
                  ->after('type')
                  ->comment('Category for discount stacking: product discounts can stack with payment discounts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discounts', function (Blueprint $table) {
            $table->dropColumn('discount_category');
        });
    }
};
