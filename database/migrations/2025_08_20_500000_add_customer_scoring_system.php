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
        // Add scoring fields to customers table
        Schema::table('customers', function (Blueprint $table) {
            $table->decimal('current_score', 5, 3)->default(0)->after('is_active');
            $table->decimal('total_value_score', 5, 3)->default(0)->after('current_score');
            $table->decimal('order_frequency_score', 5, 3)->default(0)->after('total_value_score');
            $table->decimal('order_count_score', 5, 3)->default(0)->after('order_frequency_score');
            $table->decimal('location_score', 5, 3)->default(0)->after('order_count_score');
            $table->timestamp('last_score_calculated_at')->nullable()->after('location_score');
        });

        // Check and drop columns if they exist
        if (Schema::hasColumn('customers', 'total_spent')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('total_spent');
            });
        }
        
        if (Schema::hasColumn('customers', 'order_count')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('order_count');
            });
        }

        // Add scoring configuration to customer_types table
        Schema::table('customer_types', function (Blueprint $table) {
            $table->decimal('minimum_score', 5, 3)->default(0)->after('discount_percentage');
            $table->integer('priority')->default(1)->after('minimum_score'); // Higher number = higher priority
            $table->json('scoring_weights')->nullable()->after('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop scoring fields from customers table
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'current_score',
                'total_value_score', 
                'order_frequency_score',
                'order_count_score',
                'location_score',
                'last_score_calculated_at'
            ]);
        });
        
        // Re-add the removed columns if they don't exist
        if (!Schema::hasColumn('customers', 'total_spent')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->decimal('total_spent', 12, 2)->default(0);
            });
        }
        
        if (!Schema::hasColumn('customers', 'order_count')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->integer('order_count')->default(0);
            });
        }

        // Drop scoring fields from customer_types table
        Schema::table('customer_types', function (Blueprint $table) {
            $table->dropColumn(['minimum_score', 'priority', 'scoring_weights']);
        });
    }
};
