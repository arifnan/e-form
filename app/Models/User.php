<?php

namespace App\Models;

// Import trait yang benar
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable; // Trait Notifiable dari Laravel
use Laravel\Sanctum\HasApiTokens;       // Trait HasApiTokens dari Sanctum
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class User extends Authenticatable
{
    // Gunakan trait dengan namespace yang benar
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // 'admin', 'teacher', 'student' - jika ini model generik
        // Tambahkan kolom lain yang umum jika ini model generik
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Contoh relasi jika model User ini generik dan bisa memiliki notifikasi atau favorit
    // Jika Teacher dan Student adalah model terpisah, relasi ini lebih cocok di sana.

    /**
     * Notifikasi untuk pengguna ini.
     */
    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable')
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Formulir yang difavoritkan oleh pengguna ini.
     */
    public function favoriteForms(): MorphToMany
    {
        return $this->morphToMany(Form::class, 'user', 'favorite_forms', 'user_id', 'form_id')
                    ->withTimestamps();
    }

    // Jika User ini bisa menjadi guru
    public function createdForms(): HasMany
    {
        if ($this->role === 'teacher') { // Atau cek instance jika Teacher model terpisah
            return $this->hasMany(Form::class, 'teacher_id');
        }
        return $this->hasMany(Form::class, 'teacher_id')->whereRaw('1 = 0'); // Mengembalikan query kosong jika bukan guru
    }

    // Jika User ini bisa menjadi siswa
    public function submittedResponses(): HasMany
    {
        if ($this->role === 'student') { // Atau cek instance jika Student model terpisah
             return $this->hasMany(Response::class, 'student_id');
        }
        return $this->hasMany(Response::class, 'student_id')->whereRaw('1 = 0'); // Mengembalikan query kosong jika bukan siswa
    }
}