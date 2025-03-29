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
        // For PostgreSQL, we need to modify the ENUM type
        DB::statement("ALTER TABLE orders DROP CONSTRAINT orders_payment_method_check");
        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_payment_method_check CHECK (payment_method::text = ANY (ARRAY['gcash'::character varying, 'cod'::character varying, 'cash'::character varying]::text[]))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to only allowing 'gcash'
        DB::statement("ALTER TABLE orders DROP CONSTRAINT orders_payment_method_check");
        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_payment_method_check CHECK (payment_method::text = 'gcash'::text)");
    }
};
