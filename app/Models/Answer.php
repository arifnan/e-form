<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Answer extends Model
{
    use HasFactory;

 // Nama tabel untuk model Answer, berdasarkan SQL dump Anda adalah 'response_answers'
    // Jika nama tabel Anda tidak mengikuti konvensi jamak dari nama model (misal, Answers -> answers),
    // Anda perlu mendefinisikannya secara eksplisit. Namun, 'response_answers' adalah nama yang berbeda.
    // Kemungkinan Laravel akan mencoba mencari tabel 'answers'.
    // Untuk mencocokkan dengan SQL Anda, tabelnya adalah 'response_answers'
    // dan kolom foreign key ke tabel 'responses' adalah 'response_id'.
    protected $table = 'response_answers'; // Tambahkan ini jika nama tabel berbeda dari konvensi Laravel 'answers'


    protected $fillable = [
        'response_id', // Ini adalah foreign key ke tabel 'responses'
        'question_id',
        'answer_text',
        'option_id',       // dari SQL dump
        'file_url',        // dari SQL dump
        'latitude',        // dari SQL dump
        'longitude',       // dari SQL dump
        'formatted_address'// dari SQL dump
    ];

    /**
     * Mendapatkan data pengiriman (response) yang memiliki jawaban ini.
     */
    public function response(): BelongsTo // Mengubah nama method agar lebih sesuai
    {
        // Merujuk ke model Response (yang berinteraksi dengan tabel 'responses')
        // Foreign key di tabel 'response_answers' adalah 'response_id'
        return $this->belongsTo(Response::class, 'response_id');
    }

    /**
     * Mendapatkan pertanyaan yang dijawab oleh jawaban ini.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    
}