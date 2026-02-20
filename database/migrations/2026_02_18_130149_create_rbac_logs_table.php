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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('rbac_logs');
        Schema::enableForeignKeyConstraints();
        Schema::create('rbac_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->constrained('users')->cascadeOnDelete(); // Siapa yang mengubah (Admin)
            $table->foreignId('target_user_id')->nullable(); // Siapa yang diubah
            $table->string('event'); // 'assigned_role', 'revoked_permission'
            $table->json('properties'); // Data lama vs data baru
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rbac_logs');
    }
};
