<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->onDelete('cascade');
            $table->text('question_text');
            $table->enum('question_type', ['multiple_choice', 'short_text', 'long_text', 'dropdown', 'checkbox', 'true_false', 'file_upload']);
            $table->boolean('is_required')->default(false);
            $table->boolean('requires_location')->default(false); // Tambahan untuk lokasi
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('questions');
    }
};

