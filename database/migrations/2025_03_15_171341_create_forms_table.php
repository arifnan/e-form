<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('forms')) {
            Schema::create('forms', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id')->comment('ID guru pembuat form')->constrained('teachers')->onDelete('cascade');
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('form_code')->unique();
                $table->timestamps();
            });
        } else {
             // Jika tabel forms sudah ada, pastikan kolom teacher_id ada dan benar
             Schema::table('forms', function (Blueprint $table) {
                 // Jika sebelumnya menggunakan user_id dan ingin menggantinya:
                 if (Schema::hasColumn('forms', 'user_id') && !Schema::hasColumn('forms', 'teacher_id')) {
                     // Mungkin perlu drop foreign key user_id dulu jika ada
                     // $table->dropForeign(['user_id']);
                     // $table->renameColumn('user_id', 'teacher_id');
                     // $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
                     // Atau jika 'user_id' belum jadi foreign key:
                     // $table->renameColumn('user_id', 'teacher_id');
                 } elseif (!Schema::hasColumn('forms', 'teacher_id')) {
                     $table->foreignId('teacher_id')->after('id')->comment('ID guru pembuat form')->constrained('teachers')->onDelete('cascade');
                 }

                 // Pastikan kolom form_code ada
                 if (!Schema::hasColumn('forms', 'form_code')) {
                      $table->string('form_code')->unique()->after('description'); // Sesuaikan posisi
                 }
             });
        }
    }
    public function down(): void
    {
        Schema::dropIfExists('forms');
    }
};