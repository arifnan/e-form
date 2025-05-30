<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Berdasarkan tabel 'questions' di SQL dump Anda
        return [
            'id' => $this->id,
            'form_id' => $this->form_id,
            'question_text' => $this->question_text,
            'question_type' => $this->question_type, // enum('multiple_choice','short_text',...)
            'is_required' => (bool) $this->is_required,
            // 'requires_location' => (bool) $this->requires_location, // Ada di SQL dump Anda
            'options' => QuestionOptionResource::collection($this->whenLoaded('options')), // Asumsi ada relasi 'options' di model Question ke model QuestionOption
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),

            // Untuk tipe pertanyaan yang mungkin memiliki struktur opsi berbeda di mobile (misal LinearScale dari Android)
            // Anda mungkin perlu menyesuaikan ini berdasarkan bagaimana question_type dan options disimpan/diinterpretasikan
            // Contoh jika 'LinearScale' di mobile disimpan dengan opsi tertentu di Laravel
            // 'min_value' => $this->when($this->question_type === 'linear_scale', $this->options->get(0)->option_text ?? null),
            // 'max_value' => $this->when($this->question_type === 'linear_scale', $this->options->get(1)->option_text ?? null),
            // 'min_label' => $this->when($this->question_type === 'linear_scale', $this->options->get(2)->option_text ?? null),
            // 'max_label' => $this->when($this->question_type === 'linear_scale', $this->options->get(3)->option_text ?? null),
        ];
    }
}