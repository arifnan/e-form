<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('students')) { // Hanya buat jika belum ada
            Schema::create('students', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('student_id_number')->unique()->nullable(); // Contoh: NISN
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->text('address')->nullable();
                $table->string('profile_photo_path', 2048)->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};