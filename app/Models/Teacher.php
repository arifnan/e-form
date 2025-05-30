<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;       // <--- TAMBAHKAN IMPORT INI
use Illuminate\Notifications\Notifiable;  // <--- TAMBAHKAN IMPORT INI
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Teacher extends Authenticatable
{
     use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'teachers'; // Sesuai SQL dump Anda

    protected $fillable = [
        'nip',
        'name',
        'gender',
        'email',
        'password',
        'subject',
        'address'
    ];

    protected $hidden = [
        'password',
        'remember_token' // Standar untuk ditambahkan
    ];

    protected $casts = [
        'password' => 'hashed',
        'gender' => 'boolean',
    ];

    // Relasi yang sudah Anda definisikan atau butuhkan
    public function createdForms(): HasMany
    {
        return $this->hasMany(Form::class, 'teacher_id');
    }

    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable')
                    ->orderBy('created_at', 'desc');
    }

    public function favoriteForms(): MorphToMany
    {
        return $this->morphToMany(Form::class, 'user', 'favorite_forms', 'user_id', 'form_id')
                    ->withTimestamps();
    }

    // Accessor untuk konsistensi dengan UserResource jika diperlukan
    public function getRoleAttribute(): string
    {
        return 'teacher';
    }

    // Accessor lain yang mungkin dibutuhkan UserResource jika Teacher tidak punya kolom tersebut
    public function getGradeAttribute(): ?string
    {
        return null; // Guru tidak punya 'grade'
    }
}