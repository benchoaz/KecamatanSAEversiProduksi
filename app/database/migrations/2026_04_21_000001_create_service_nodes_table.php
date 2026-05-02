<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_layanan_id')->constrained('master_layanan')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('service_nodes')->onDelete('cascade');
            $table->smallInteger('depth')->default(0);        // 0=root, 1=level1, 2=level2 dst
            $table->string('name');                           // "Buat KK Baru", "Kelahiran"
            $table->text('description')->nullable();
            $table->string('ikon', 100)->nullable();          // FontAwesome class
            $table->integer('urutan')->default(0);
            $table->boolean('is_leaf')->default(false);       // TRUE = tampilkan form + syarat
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('parent_id');
            $table->index('master_layanan_id');
            $table->index(['parent_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_nodes');
    }
};
