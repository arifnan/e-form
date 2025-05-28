<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'question_text',
        'question_type',
        'options', // JSON array
        'is_required', // Boolean
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
    ];


    // Relasi: Satu pertanyaan bisa memiliki banyak opsi jawaban (jika multiple_choice, dropdown, checkbox)
    public function options()
    {
        return $this->hasMany(QuestionOption::class);
    }

    // Relasi: Satu pertanyaan bisa memiliki banyak jawaban dari user
    public function answers()
    {
        return $this->hasMany(ResponseAnswer::class);
    }

    // Relasi: Pertanyaan milik satu formulir
    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
