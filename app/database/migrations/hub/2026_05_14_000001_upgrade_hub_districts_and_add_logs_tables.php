<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Upgrade hub_districts — tambah kolom WAHA, n8n, AI config
        Schema::table('hub_districts', function (Blueprint $table) {
            $table->string('waha_session_name')->nullable()->after('domain')
                  ->comment('Nama session WAHA, misal: default');
            $table->string('operator_phone')->nullable()->after('waha_session_name')
                  ->comment('Nomor WA operator kecamatan untuk notifikasi');
            $table->boolean('ai_enabled')->default(true)->after('operator_phone')
                  ->comment('Toggle AI responder untuk kecamatan ini');
            $table->text('n8n_webhook_url')->nullable()->after('ai_enabled')
                  ->comment('URL webhook n8n spesifik untuk kecamatan ini');
            $table->jsonb('l1_keywords')->nullable()->after('n8n_webhook_url')
                  ->comment('Keyword → jawaban cepat tanpa AI, format: {"jam buka": "08:00-16:00"}');
        });

        // 2. Tabel log semua pesan masuk/keluar (statistik real)
        Schema::create('hub_message_logs', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(\Illuminate\Support\Facades\DB::raw('gen_random_uuid()'));
            $table->foreignUuid('hub_district_id')
                  ->nullable()
                  ->constrained('hub_districts')
                  ->onDelete('set null');
            $table->string('phone_number', 30)->index();
            $table->text('message_in')->nullable();
            $table->text('message_out')->nullable();
            $table->string('handler_layer', 10)->default('l2')
                  ->comment('l1=rule, l2=template, l3=ai, manual=operator');
            $table->boolean('is_complaint')->default(false);
            $table->string('ticket_number', 30)->nullable()->index();
            $table->integer('response_time_ms')->nullable();
            $table->string('waha_message_id')->nullable()
                  ->comment('ID pesan dari WAHA untuk dedup');
            $table->timestampTz('created_at')->useCurrent();
        });

        // 3. Pemetaan desa → kecamatan (fondasi routing L1)
        Schema::create('hub_village_map', function (Blueprint $table) {
            $table->id();
            $table->string('village_name', 100)->index()
                  ->comment('Nama desa dalam huruf kecil tanpa spasi');
            $table->jsonb('aliases')->nullable()
                  ->comment('Array alias: ["alasnyiur", "alas nyiur", "alasniur"]');
            $table->foreignUuid('hub_district_id')
                  ->constrained('hub_districts')
                  ->onDelete('cascade');
            $table->timestamps();
        });

        // Index untuk performa query log
        \Illuminate\Support\Facades\DB::statement(
            'CREATE INDEX idx_hub_msg_logs_district_date
             ON hub_message_logs (hub_district_id, created_at DESC)'
        );
        \Illuminate\Support\Facades\DB::statement(
            'CREATE INDEX idx_hub_msg_logs_complaint
             ON hub_message_logs (is_complaint, created_at DESC)
             WHERE is_complaint = true'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('hub_village_map');
        Schema::dropIfExists('hub_message_logs');

        Schema::table('hub_districts', function (Blueprint $table) {
            $table->dropColumn([
                'waha_session_name',
                'operator_phone',
                'ai_enabled',
                'n8n_webhook_url',
                'l1_keywords',
            ]);
        });
    }
};
