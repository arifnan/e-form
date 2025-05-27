<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens; // Import HasApiTokens
use Illuminate\Notifications\Notifiable; // Import Notifiable jika belum ada

class Teacher extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable; // Tambahkan HasApiTokens dan Notifiable

    protected $fillable = ['nip', 'name', 'gender', 'email', 'password', 'subject', 'address'];

    protected $hidden = ['password', 'remember_token']; // Tambahkan remember_token jika belum ada

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // 'email_verified_at' => 'datetime', // Tambahkan jika ada verifikasi email
            'password' => 'hashed',
        ];
    }

    // Relasi untuk mengambil form yang dibuat oleh guru
    public function forms()
    {
        return $this->hasMany(Form::class);
    }
}