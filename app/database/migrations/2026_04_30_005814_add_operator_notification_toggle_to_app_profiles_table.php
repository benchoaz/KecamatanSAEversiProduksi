<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_profiles', function (Blueprint $table) {
            $table->boolean('is_operator_notification_enabled')->default(true)->after('whatsapp_complaint');
        });
    }

    public function down(): void
    {
        Schema::table('app_profiles', function (Blueprint $table) {
            $table->dropColumn('is_operator_notification_enabled');
        });
    }
};
