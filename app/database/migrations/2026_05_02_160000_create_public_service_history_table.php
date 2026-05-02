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
        Schema::create('public_service_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('public_service_id')->constrained('public_services')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users'); // Who performed the action
            $table->string('status_from')->nullable();
            $table->string('status_to')->nullable();
            $table->text('comment')->nullable(); // Admin internal comment or action log
            $table->string('action_type')->default('status_change'); // status_change, manual_comment, feedback_response
            $table->json('metadata')->nullable(); // Any additional data
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('public_service_history');
    }
};
