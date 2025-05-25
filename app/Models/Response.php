<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id'
    ];

    // Relasi: Respon milik satu formulir
    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    // Relasi: Satu respon bisa memiliki banyak jawaban
    public function answers()
    {
        return $this->hasMany(ResponseAnswer::class);
    }
}
