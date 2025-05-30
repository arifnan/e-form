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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            // Kolom polimorfik untuk mengaitkan notifikasi dengan Teacher atau Student
            $table->morphs('notifiable'); // Akan membuat notifiable_id (BIGINT UNSIGNED) dan notifiable_type (VARCHAR)
            $table->string('title');
            $table->text('message');
            $table->timestamp('read_at')->nullable(); // Kapan notifikasi dibaca
            $table->timestamps(); // created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};