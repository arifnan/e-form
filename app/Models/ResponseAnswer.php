<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResponseAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'response_id',
        'question_id',
        'answer_text',
        'option_id',
        'file_url',
        'latitude',
        'longitude',
        'formatted_address'
    ];

    // Relasi: Jawaban milik satu respon
    public function response()
    {
        return $this->belongsTo(Response::class);
    }

    // Relasi: Jawaban milik satu pertanyaan
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    // Relasi: Jika jawaban berupa pilihan, maka berelasi dengan QuestionOption
    public function option()
    {
        return $this->belongsTo(QuestionOption::class);
    }
}
