<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('forms', function (Blueprint $table) {
            $table->foreignId('teacher_id')->constrained('teachers'); // Menambahkan kolom teacher_id
        });
    }

    public function down() {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn('teacher_id'); // Menghapus kolom teacher_id jika migrasi dibatalkan
        });
    }
};
