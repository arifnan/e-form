<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
// ============== TAMBAHKAN IMPORT DI BAWAH INI ==============
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
// ==========================================================
// Tambahkan juga use statement untuk relasi jika belum ada
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use App\Models\Form;
use App\Models\Response;
use App\Models\Notification;


class Student extends Authenticatable
{
    // ============== TAMBAHKAN TRAIT DI BAWAH INI ==============
    use HasApiTokens, HasFactory, Notifiable;
    // ==========================================================
    // use HasFactory; // Hapus ini jika sudah ada di atas

    protected $table = 'students'; // Anda bisa tambahkan ini untuk eksplisit

    protected $fillable = ['name', 'gender', 'email', 'password', 'grade', 'address'];

    protected $hidden = [
        'password',
        'remember_token', // Tambahkan ini, standar Laravel
    ];

    // Tambahkan $casts jika belum ada
    protected $casts = [
        'password' => 'hashed',
        'gender' => 'boolean',
    ];

    // Tambahkan relasi jika belum ada (sesuaikan dengan kebutuhan Anda)
    public function submittedResponses(): HasMany
    {
        return $this->hasMany(Response::class, 'student_id');
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

    // Accessor yang mungkin berguna
    public function getRoleAttribute(): string { return 'student'; }
    public function getNipAttribute(): ?string { return null; }
    public function getSubjectAttribute(): ?string { return null; }
}