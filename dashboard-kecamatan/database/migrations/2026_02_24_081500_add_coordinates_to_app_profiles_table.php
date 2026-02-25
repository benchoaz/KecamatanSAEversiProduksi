<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('app_profiles', function (Blueprint $table) {
            $table->decimal('map_latitude', 10, 8)->nullable()->after('is_ai_active');
            $table->decimal('map_longitude', 11, 8)->nullable()->after('map_latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_profiles', function (Blueprint $table) {
            $table->dropColumn(['map_latitude', 'map_longitude']);
        });
    }
};
