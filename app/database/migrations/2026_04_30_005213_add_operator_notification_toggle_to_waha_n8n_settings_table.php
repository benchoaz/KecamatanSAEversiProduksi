<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('waha_n8n_settings', function (Blueprint $table) {
            $table->boolean('is_operator_notification_enabled')->default(true)->after('operator_number');
        });
    }

    public function down(): void
    {
        Schema::table('waha_n8n_settings', function (Blueprint $table) {
            $table->dropColumn('is_operator_notification_enabled');
        });
    }
};
