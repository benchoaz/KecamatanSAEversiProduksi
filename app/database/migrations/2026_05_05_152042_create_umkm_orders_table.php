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
        Schema::create('umkm_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('umkm_id')->constrained('umkm')->onDelete('cascade');
            $table->string('customer_name');
            $table->string('customer_whatsapp');
            $table->decimal('total_price', 15, 2);
            $table->text('shipping_address')->nullable();
            $table->string('shipping_method')->nullable();
            $table->string('status')->default('pending'); // pending, packing, sent, completed, cancelled
            $table->string('tracking_number')->nullable();
            $table->text('notes')->nullable();
            $table->string('payment_status')->default('unpaid');
            $table->string('payment_method')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('umkm_orders');
    }
};
