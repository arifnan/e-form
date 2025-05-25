<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('response_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('response_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->text('answer_text')->nullable();
            $table->foreignId('option_id')->nullable()->constrained('question_options')->onDelete('cascade');
            $table->string('file_url')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();  // Lokasi pengguna
            $table->decimal('longitude', 11, 8)->nullable(); // Lokasi pengguna
            $table->string('formatted_address')->nullable(); // Alamat lengkap pengguna
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('response_answers');
    }
};
