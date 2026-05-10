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
        Schema::table('application_profiles', function (Blueprint $table) {
            $table->string('document_ai_provider')->default('none')->after('ai_provider');
            $table->string('document_ai_key')->nullable()->after('document_ai_provider');
            $table->boolean('is_document_ai_active')->default(false)->after('document_ai_key');
            $table->text('validation_sop_text')->nullable()->after('is_document_ai_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('application_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'document_ai_provider',
                'document_ai_key',
                'is_document_ai_active',
                'validation_sop_text'
            ]);
        });
    }
};
