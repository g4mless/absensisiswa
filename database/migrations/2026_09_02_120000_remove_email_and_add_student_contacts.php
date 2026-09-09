<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('phone', 30)->nullable()->after('class_id');
            $table->text('address')->nullable()->after('phone');
        });

        // SQLite (test env) tidak bisa rebuild tabel selama index unik kolom
        // yang di-drop masih ada: hapus index-nya dulu. No-op di pgsql.
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropUnique(['email']);
                });
            } catch (\Throwable $e) {
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['email', 'email_verified_at']);
        });

        Schema::dropIfExists('password_reset_tokens');
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->unique()->after('name');
            $table->timestamp('email_verified_at')->nullable()->after('email');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['phone', 'address']);
        });
    }
};
