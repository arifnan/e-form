<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // $this->resource merujuk pada instance model User (atau Teacher/Student)
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'nip' => $this->when($this->role === 'teacher' && isset($this->nip), $this->nip), // Hanya tampilkan NIP jika peran adalah guru dan NIP ada
            'role' => $this->role, // Kolom role akan ada jika model User Anda memilikinya
            'gender' => $this->gender, // Berdasarkan SQL dump, ini adalah boolean (1 atau 0)
            'address' => $this->address,
            'subject' => $this->when($this->role === 'teacher' && isset($this->subject), $this->subject), // Hanya untuk guru
            'grade' => $this->when($this->role === 'student' && isset($this->grade), $this->grade),     // Hanya untuk siswa
            // 'profile_photo_url' => $this->profile_photo_url, // Jika Anda memiliki accessor untuk ini di model User
            // 'profile_photo_path' => $this->profile_photo_path, // Jika kolom ini ada dan ingin diekspos
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}