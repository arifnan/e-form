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
        Schema::create('favorite_forms', function (Blueprint $table) {
            $table->id();
            // Kolom polimorfik untuk pengguna (Teacher atau Student)
            $table->morphs('user'); // Akan membuat user_id dan user_type
            $table->unsignedBigInteger('form_id');
            $table->timestamps();

            // Foreign key constraint ke tabel forms
            $table->foreign('form_id')->references('id')->on('forms')->onDelete('cascade');

            // Unique constraint untuk memastikan satu pengguna hanya bisa memfavoritkan satu formulir sekali
            $table->unique(['user_id', 'user_type', 'form_id'], 'user_form_favorite_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorite_forms');
    }
};