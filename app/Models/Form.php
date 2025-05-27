<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; // Import Str

class Form extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'teacher_id', 'form_code'];

    // Relasi: Setiap Formulir dibuat oleh satu Guru
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
    
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function responses()
    {
        return $this->hasMany(Response::class);
    }

    // Boot method to generate form_code when creating a new form
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($form) {
            if (empty($form->form_code)) {
                $form->form_code = Str::random(8); // Membuat kode acak 8 karakter
            }
        });
    }
}