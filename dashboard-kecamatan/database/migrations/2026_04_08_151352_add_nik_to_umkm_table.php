<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('umkm', function (Blueprint $table) {
            $table->string('nik', 16)->nullable()->after('nama_pemilik');
        });
    }

    public function down()
    {
        Schema::table('umkm', function (Blueprint $table) {
            $table->dropColumn('nik');
        });
    }
};
