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
        Schema::table('waha_n8n_settings', function (Blueprint $table) {
            $table->text('broadcast_group_ids')->nullable();
            $table->boolean('is_weather_alert_enabled')->default(false);
            $table->timestamp('last_weather_alert_check')->nullable();
            $table->string('last_alert_id')->nullable(); // To avoid duplicate alerts
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('waha_n8n_settings', function (Blueprint $table) {
            $table->dropColumn(['broadcast_group_ids', 'is_weather_alert_enabled', 'last_weather_alert_check', 'last_alert_id']);
        });
    }
};
