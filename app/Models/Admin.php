<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens; // Import HasApiTokens
use Illuminate\Notifications\Notifiable; // Import Notifiable jika belum ada

class Student extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable; // Tambahkan HasApiTokens dan Notifiable

    protected $fillable = ['name', 'gender', 'email', 'password', 'grade', 'address'];

    protected $hidden = ['password', 'remember_token']; // Tambahkan remember_token jika belum ada

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // 'email_verified_at' => 'datetime', // Dihapus karena fitur verifikasi email belum dibutuhkan
            'password' => 'hashed',
        ];
    }

    public function responses()
    {
        return $this->hasMany(Response::class);
    }
}