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
        Schema::create('navigation_menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->string('slug')->unique();
            $table->integer('order')->default(0);
            $table->string('permission_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('target_dashboard')->default('kecamatan'); // kecamatan or desa
            $table->timestamps();
        });

        Schema::create('navigation_sub_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('navigation_menus')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('route_name')->nullable();
            $table->integer('order')->default(0);
            $table->string('permission_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('navigation_sub_menus');
        Schema::dropIfExists('navigation_menus');
    }
};
