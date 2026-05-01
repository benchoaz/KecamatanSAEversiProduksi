<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('waha_n8n_settings', function (Blueprint $table) {
            $table->string('operator_number')->nullable()->after('bot_number');
        });
    }

    public function down(): void
    {
        Schema::table('waha_n8n_settings', function (Blueprint $table) {
            $table->dropColumn('operator_number');
        });
    }
};
