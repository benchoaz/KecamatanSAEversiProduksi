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
        // 1. Districts Table (The Hub Districts)
        Schema::create('hub_districts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('domain')->nullable()->unique();

            // Database Connection Info
            $table->string('db_connection_name')->default('pgsql');
            $table->string('db_host')->nullable();
            $table->string('db_port')->default('5432');
            $table->string('db_name')->nullable();
            $table->string('db_user')->nullable();
            $table->text('db_pass')->nullable(); // Encrypted

            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        // 2. WhatsApp Sessions Table (Routing Logic)
        Schema::create('hub_wa_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('phone_number')->index();
            $table->foreignUuid('hub_district_id')->constrained('hub_districts')->onDelete('cascade');
            $table->timestamp('last_interaction_at')->nullable();
            $table->json('context_data')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['phone_number', 'hub_district_id']);
        });

        // 3. Global AI Configurations
        Schema::create('hub_ai_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('description')->nullable();
            $table->boolean('is_global')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hub_ai_configs');
        Schema::dropIfExists('hub_wa_sessions');
        Schema::dropIfExists('hub_districts');
    }
};
