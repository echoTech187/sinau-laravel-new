<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('user_has_permissions');
        Schema::dropIfExists('user_has_roles');
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('modules');
        Schema::enableForeignKeyConstraints();
        // 1. MODULES (Untuk Grouping Menu - Req #3)
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name');         // Contoh: "Master Data", "Settings"
            $table->string('label');        // Nama tampilan (bisa translate)
            $table->string('icon')->nullable(); // Icon untuk header grup (jika perlu)
            $table->integer('order')->default(0); // Urutan tampil
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. PERMISSIONS (Satuan terkecil akses - Req #1)
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete(); // Grouping permission per modul
            $table->string('name'); // Human readable: "Create Product"
            $table->string('slug')->unique(); // System readable: "product.create"
            $table->string('group_name')->nullable(); // Sub-group: "Product", "User"
            $table->timestamps();
        });

        // 4. MENUS (Struktur Sidebar Dinamis - Req #1, #4)
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('menus')->cascadeOnDelete(); // Untuk Sub-menu
            $table->foreignId('permission_id')->nullable()->constrained('permissions')->cascadeOnDelete(); // KUNCI: Menu ini tampil kalau user punya permission ini

            $table->string('name');         // Label Menu: "Data Produk"
            $table->string('icon')->nullable(); // Class icon: "fas fa-box"
            $table->string('route')->nullable(); // Nama Route Laravel: "products.index"
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->enum('target', ['_self', '_blank'])->default('_self');
            $table->timestamps();
        });

        // 5. ROLE_HAS_PERMISSIONS (Pivot)
        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        // 6. USER_HAS_ROLES (Pivot)
        Schema::create('user_has_roles', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->json('data_scope')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->primary(['user_id', 'role_id']);
        });

        // 7. USER_HAS_PERMISSIONS (Req #2 - Bypass/Special Access)
        // Tabel ini memungkinkan User A punya akses 'delete' meskipun Rolenya tidak punya.
        Schema::create('user_has_permissions', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->boolean('is_forbidden')->default(false); // Opsional: Bisa buat blacklist user tertentu dari permission spesifik
            $table->primary(['user_id', 'permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_has_permissions');
        Schema::dropIfExists('user_has_roles');
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('modules');
    }
};
