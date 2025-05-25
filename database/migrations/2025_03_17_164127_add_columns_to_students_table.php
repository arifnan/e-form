<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('students', function (Blueprint $table) {
            $table->boolean('gender')->after('name'); // 0 = Perempuan, 1 = Laki-laki
            $table->text('address')->nullable()->after('password'); // Alamat
        });
    }

    public function down() {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['gender', 'address']);
        });
    }
};

