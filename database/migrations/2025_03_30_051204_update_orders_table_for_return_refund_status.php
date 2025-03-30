<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // First, drop existing return_refund_status enum constraint if it exists
            if (Schema::hasColumn('orders', 'return_refund_status')) {
                // For PostgreSQL, we need to drop the constraint first
                if (DB::getDriverName() === 'pgsql') {
                    DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_return_refund_status_check');
                }

                // For MySQL, we can modify the column directly
                if (DB::getDriverName() === 'mysql') {
                    DB::statement("ALTER TABLE orders MODIFY return_refund_status ENUM('none', 'requested', 'approved', 'rejected', 'refunded') DEFAULT 'none'");
                } else {
                    // For other DB systems, we might need to drop and recreate the column
                    $table->dropColumn('return_refund_status');
                    $table->enum('return_refund_status', ['none', 'requested', 'approved', 'rejected', 'refunded'])->default('none')->after('order_status');
                }
            } else {
                // If the column doesn't exist, create it
                $table->enum('return_refund_status', ['none', 'requested', 'approved', 'rejected', 'refunded'])->default('none')->after('order_status');
            }

            // Add a nullable delivered_at column to track when an order was delivered
            if (!Schema::hasColumn('orders', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('return_refund_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'delivered_at')) {
                $table->dropColumn('delivered_at');
            }

            // We're not going to drop return_refund_status, as it might have been there before.
            // Instead, revert it to its original state if necessary
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE orders MODIFY return_refund_status ENUM('none', 'requested', 'approved', 'refunded') DEFAULT 'none'");
            }
        });
    }
};
