// ArifNAn/e-form/e-form-3d149c25473dfc11a20270bf4e5ce914d4c71076/app/Models/Response.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'student_id', // Tambahkan student_id
    ];

    // Relasi: Respon milik satu formulir
    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    // Relasi: Respon milik satu siswa
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // Relasi: Satu respon bisa memiliki banyak jawaban
    public function answers()
    {
        return $this->hasMany(ResponseAnswer::class);
    }
}