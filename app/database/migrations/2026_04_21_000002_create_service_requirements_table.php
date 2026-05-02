<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('node_id')->constrained('service_nodes')->onDelete('cascade');

            // Tipe syarat: file_upload | text_info | checkbox
            $table->string('type', 30)->default('file_upload');

            // Label yang tampil ke warga: "Fotokopi KTP", "Surat Pengantar RT/RW"
            $table->string('label');

            // Keterangan tambahan untuk warga
            $table->text('description')->nullable();

            // Apakah wajib?
            $table->boolean('is_required')->default(true);

            // Untuk type file_upload: format yang diterima (csv)
            $table->string('accepted_types', 100)->nullable()->default('jpg,png,pdf');

            // Batas maksimum ukuran file (MB)
            $table->unsignedSmallInteger('max_size_mb')->default(5);

            // Urutan tampil
            $table->integer('urutan')->default(0);

            $table->timestamps();

            $table->index(['node_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requirements');
    }
};
