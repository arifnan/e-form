<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage; // Untuk mengambil URL jika file disimpan

class AnswerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Merujuk pada tabel 'response_answers'
        return [
            'id' => $this->id,
            'response_id' => $this->response_id,
            'question_id' => $this->question_id,
            'answer_text' => $this->answer_text,
            'option_id' => $this->option_id,
            // Jika file_url adalah path relatif di storage, buat URL lengkap
            'file_url' => $this->file_url ? Storage::url($this->file_url) : null,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'formatted_address' => $this->formatted_address,
            'created_at' => $this->created_at->toDateTimeString(),
            'question' => new QuestionResource($this->whenLoaded('question')), // Eager load detail pertanyaan
        ];
    }
}