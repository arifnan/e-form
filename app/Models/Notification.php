<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',    // Akan menyimpan ID dari Teacher atau Student
        'user_type',  // Akan menyimpan namespace model (e.g., App\Models\Teacher atau App\Models\Student)
        'title',
        'message',
        'data', // JSON untuk data tambahan (misal, form_id, response_id)
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * Dapatkan model parent user (bisa Teacher atau Student).
     * Nama metode 'user' harus cocok dengan argumen kedua di morphMany/morphToMany pada model Teacher/Student.
     */
    public function userable(): MorphTo // Menggunakan nama 'userable' untuk menghindari konflik jika ada relasi 'user()' biasa
    {
        return $this->morphTo(__FUNCTION__, 'user_type', 'user_id');
    }
}