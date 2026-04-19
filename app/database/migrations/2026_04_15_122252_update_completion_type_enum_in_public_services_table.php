<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE public_services ALTER COLUMN completion_type TYPE VARCHAR(255)");
            DB::statement("ALTER TABLE public_services ALTER COLUMN completion_type DROP NOT NULL");
        } else {
            DB::statement("ALTER TABLE public_services MODIFY COLUMN completion_type ENUM(\"digital\", \"physical\", \"whatsapp\") NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
             DB::statement("ALTER TABLE public_services ALTER COLUMN completion_type TYPE VARCHAR(255)");
        } else {
            DB::statement("ALTER TABLE public_services MODIFY COLUMN completion_type ENUM(\"digital\", \"physical\") NULL");
        }
    }
};
