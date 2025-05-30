<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth; // Untuk mendapatkan user yang terautentikasi

class FormResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = Auth::user(); // Dapatkan pengguna yang sedang login
        $isFavoritedByCurrentUser = false;

        if ($user) {
            // Cek apakah formulir ini ada dalam daftar favorit pengguna yang sedang login
            // Asumsi relasi 'favoriteForms' ada di model User (atau Teacher/Student)
            if (method_exists($user, 'favoriteForms')) {
                 $isFavoritedByCurrentUser = $user->favoriteForms()->where('forms.id', $this->id)->exists();
            }
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'form_code' => $this->form_code,
            'teacher_id' => $this->teacher_id, // ID dari guru pembuat
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'teacher' => new UserResource($this->whenLoaded('teacher')), // Asumsi relasi 'teacher' ada di model Form
            'questions' => QuestionResource::collection($this->whenLoaded('questions')), // Eager load questions
            'is_favorited_by_current_user' => $isFavoritedByCurrentUser, // Status apakah difavoritkan oleh user saat ini
            'total_responses' => $this->whenLoaded('responses', function () {
                return $this->responses->count();
            }, $this->responses()->count()), // Menghitung jumlah respons jika relasi 'responses' ada di model Form
        ];
    }
}