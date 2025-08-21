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
        Schema::table('products', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        // Generate slugs for existing products
        $products = \App\Models\Product::whereNull('slug')->orWhere('slug', '')->get();
        foreach ($products as $product) {
            $product->slug = $product->generateSlug();
            $product->save();
        }

        // Now add unique constraint
        Schema::table('products', function (Blueprint $table) {
            $table->unique('slug');
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['slug']);
            $table->dropColumn('slug');
        });
    }
};
