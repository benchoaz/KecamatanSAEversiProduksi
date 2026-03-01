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
        Schema::table('work_directory', function (Blueprint $table) {
            $table->string('owner_pin', 6)->nullable()->after('contact_phone');
            $table->timestamp('last_toggle_at')->nullable()->after('owner_pin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_directory', function (Blueprint $table) {
            $table->dropColumn(['owner_pin', 'last_toggle_at']);
        });
    }
};
