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
        Schema::table('umkm', function (Blueprint $table) {
            $table->json('shipping_methods')->nullable();
            $table->json('payment_methods')->nullable();
            $table->text('auto_reply_message')->nullable();
        });

        Schema::table('umkm_products', function (Blueprint $table) {
            $table->integer('stock')->default(0);
            $table->string('sku')->nullable();
            $table->integer('weight')->default(0);
            $table->boolean('is_preorder')->default(false);
            $table->decimal('discount_price', 15, 2)->nullable();
            $table->integer('discount_percentage')->nullable();
            $table->json('variations')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('umkm', function (Blueprint $table) {
            $table->dropColumn(['shipping_methods', 'payment_methods', 'auto_reply_message']);
        });

        Schema::table('umkm_products', function (Blueprint $table) {
            $table->dropColumn(['stock', 'sku', 'weight', 'is_preorder', 'discount_price', 'discount_percentage', 'variations']);
        });
    }
};
