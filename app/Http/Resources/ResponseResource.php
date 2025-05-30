<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResponseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Merujuk pada tabel 'responses' dan relasinya di model Response.php
        return [
            'id' => $this->id,
            'form_id' => $this->form_id,
            'student_id' => $this->student_id,
            'submitted_at' => $this->created_at->toDateTimeString(), // Menggunakan created_at sebagai waktu submit
            // Informasi foto, lokasi, dll. akan ada di dalam AnswerResource jika disimpan per jawaban.
            // Jika disimpan di tabel 'responses', tambahkan di sini.
            // Berdasarkan SQL Anda, photo_url, latitude, longitude ada di 'response_answers'.

            // Eager load relasi yang dibutuhkan
            'form' => new FormResource($this->whenLoaded('form')),
            'student' => new UserResource($this->whenLoaded('student')), // Asumsi Student adalah instance dari User atau ada StudentResource
            'answers' => AnswerResource::collection($this->whenLoaded('answers')),
        ];
    }
}