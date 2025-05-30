<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

// Impor model lain yang dibutuhkan
use App\Models\User; // Atau Teacher jika relasi teacher() ke Teacher::class
use App\Models\Question;
use App\Models\Response;

class Form extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'form_code',
        'teacher_id',
    ];

    public function teacher(): BelongsTo
    {
        // Sesuaikan dengan model yang merepresentasikan guru Anda (User atau Teacher)
        return $this->belongsTo(User::class, 'teacher_id'); // Atau Teacher::class
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }

    /**
     * Pengguna (Teacher atau Student) yang memfavoritkan formulir ini.
     */
    public function favoritedByUsers(): MorphToMany
    {
        // 'user' adalah nama yang kita gunakan di $table->morphs('user') pada migrasi favorite_forms
        // 'favorite_forms' adalah nama tabel pivot
        return $this->morphedByMany(User::class, 'user', 'favorite_forms') // Ganti User::class dengan Teacher::class dan Student::class jika perlu dan jika mereka tidak extend dari User dasar.
                    ->withTimestamps();
    }
}