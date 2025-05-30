<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('questions')) {
             Schema::create('questions', function (Blueprint $table) {
                 $table->id();
                 $table->foreignId('form_id')->constrained('forms')->onDelete('cascade');
                 $table->text('question_text');
                 $table->string('question_type');
                 $table->json('options')->nullable();
                 $table->boolean('is_required')->default(false);
                 $table->timestamps();
             });
        } else {
            Schema::table('questions', function (Blueprint $table) {
                if (!Schema::hasColumn('questions', 'options')) {
                    $table->json('options')->nullable()->after('question_type');
                }
                if (!Schema::hasColumn('questions', 'is_required')) {
                    $table->boolean('is_required')->default(false)->after('options');
                }
            });
        }
    }
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};