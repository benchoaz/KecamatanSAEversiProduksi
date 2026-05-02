<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('public_services', function (Blueprint $table) {
            $table->integer('rating')->nullable()->after('status');
            $table->text('citizen_feedback')->nullable()->after('rating');
            $table->timestamp('feedback_at')->nullable()->after('citizen_feedback');
        });
    }

    public function down(): void
    {
        Schema::table('public_services', function (Blueprint $table) {
            $table->dropColumn(['rating', 'citizen_feedback', 'feedback_at']);
        });
    }
};
