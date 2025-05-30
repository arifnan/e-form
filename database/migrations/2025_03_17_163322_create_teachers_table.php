<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('teachers')) {
            Schema::create('teachers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('nip')->unique(); // NIP spesifik untuk guru
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->text('address')->nullable(); // Kolom alamat
                $table->string('profile_photo_path', 2048)->nullable(); // Kolom foto profil
                $table->rememberToken();
                $table->timestamps();
            });
        } else {
            // Jika tabel sudah ada, tambahkan kolom yang mungkin belum ada
            Schema::table('teachers', function (Blueprint $table) {
                if (!Schema::hasColumn('teachers', 'address')) {
                    $table->text('address')->nullable()->after('password'); // Sesuaikan posisi
                }
                if (!Schema::hasColumn('teachers', 'profile_photo_path')) {
                    $table->string('profile_photo_path', 2048)->nullable()->after('address');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};