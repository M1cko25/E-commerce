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
        Schema::create('product_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('rating')->comment('Rating from 1 to 5');
            $table->text('review')->nullable();
            $table->boolean('verified_purchase')->default(false);
            $table->timestamps();

            // Ensure a customer can only rate a product once
            $table->unique(['customer_id', 'product_id']);
        });

        // Add rating fields to products table
        Schema::table('products', function (Blueprint $table) {
            $table->float('rating')->default(0)->after('price');
            $table->integer('rating_count')->default(0)->after('rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_ratings');

        // Remove the added columns
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['rating', 'rating_count']);
        });
    }
};
