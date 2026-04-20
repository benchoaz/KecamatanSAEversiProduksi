<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah flag pada master_layanan
        Schema::table('master_layanan', function (Blueprint $table) {
            $table->boolean('has_nodes')->default(false)->after('custom_link');
        });

        // 2. Tambah service_node_id pada public_services (submisi warga)
        Schema::table('public_services', function (Blueprint $table) {
            $table->foreignId('service_node_id')
                ->nullable()
                ->after('jenis_layanan')
                ->constrained('service_nodes')
                ->nullOnDelete();
        });

        // 3. Tambah requirement_id pada public_service_attachments
        Schema::table('public_service_attachments', function (Blueprint $table) {
            $table->foreignId('requirement_id')
                ->nullable()
                ->after('public_service_id')
                ->constrained('service_requirements')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('public_service_attachments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('requirement_id');
        });

        Schema::table('public_services', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_node_id');
        });

        Schema::table('master_layanan', function (Blueprint $table) {
            $table->dropColumn('has_nodes');
        });
    }
};
