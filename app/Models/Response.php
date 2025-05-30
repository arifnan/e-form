<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Response extends Model // Nama kelas adalah Response
{
    use HasFactory;

    // Nama tabel secara eksplisit jika tidak 'responses' (tapi dalam kasus Anda sudah 'responses')
    // protected $table = 'responses';

    protected $fillable = [
        'form_id',
        'student_id', // Sesuai SQL dump Anda
        'submitted_at', // Jika Anda ingin mengelolanya secara manual, jika tidak Laravel bisa handle timestamps
        // tambahkan kolom lain dari tabel 'responses' jika ada yang fillable
        // seperti photo_path, latitude, longitude, is_location_valid jika itu disimpan di tabel 'responses'
        // Namun, dari SQL dump Anda, photo, lat, long ada di 'response_answers'.
    ];

    // Timestamps (created_at, updated_at) dikelola Laravel secara default
    // public $timestamps = true; // Defaultnya true

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function student(): BelongsTo
    {
        // Foreign key di tabel 'responses' adalah 'student_id'
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function answers(): HasMany
    {
        // Satu Response memiliki banyak Answer (response_answers)
        // Foreign key di tabel 'response_answers' adalah 'response_id'
        return $this->hasMany(Answer::class, 'response_id');
    }
}