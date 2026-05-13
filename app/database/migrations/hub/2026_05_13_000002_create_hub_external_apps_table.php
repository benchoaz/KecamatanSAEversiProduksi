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
        Schema::create('hub_external_apps', function (Blueprint $blueprint) {
            $blueprint->uuid('id')->primary();
            $blueprint->string('name');
            $blueprint->string('client_id')->unique();
            $blueprint->string('client_secret');
            $blueprint->string('base_url')->nullable();
            $blueprint->string('callback_url')->nullable();
            $blueprint->enum('status', ['active', 'inactive'])->default('active');
            $blueprint->json('settings')->nullable(); // Untuk konfigurasi spesifik aplikasi
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hub_external_apps');
    }
};
