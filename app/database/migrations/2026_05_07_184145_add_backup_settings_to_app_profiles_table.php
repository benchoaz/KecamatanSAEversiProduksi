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
        Schema::table('app_profiles', function (Blueprint $table) {
            $table->text('google_drive_json')->nullable();
            $table->string('google_drive_folder_id')->nullable();
            $table->boolean('is_backup_active')->default(false);
            $table->string('backup_frequency')->default('daily');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_profiles', function (Blueprint $table) {
            $table->dropColumn(['google_drive_json', 'google_drive_folder_id', 'is_backup_active', 'backup_frequency']);
        });
    }
};
