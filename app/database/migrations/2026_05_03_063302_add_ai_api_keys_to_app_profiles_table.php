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
            $table->string('ai_provider')->default('gemini')->after('is_ai_active');
            $table->text('openai_api_key')->nullable()->after('ai_provider');
            $table->text('google_api_key')->nullable()->after('openai_api_key');
            $table->text('anthropic_api_key')->nullable()->after('google_api_key');
            $table->text('xai_api_key')->nullable()->after('anthropic_api_key');
            $table->text('deepseek_api_key')->nullable()->after('xai_api_key');
            $table->text('dashscope_api_key')->nullable()->after('deepseek_api_key');
            $table->text('zhipu_api_key')->nullable()->after('dashscope_api_key');
            $table->text('openrouter_api_key')->nullable()->after('zhipu_api_key');
            $table->text('alpha_vantage_api_key')->nullable()->after('openrouter_api_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'ai_provider',
                'openai_api_key',
                'google_api_key',
                'anthropic_api_key',
                'xai_api_key',
                'deepseek_api_key',
                'dashscope_api_key',
                'zhipu_api_key',
                'openrouter_api_key',
                'alpha_vantage_api_key',
            ]);
        });
    }
};
