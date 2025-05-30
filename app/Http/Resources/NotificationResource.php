<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            // 'notifiable_id' => $this->notifiable_id, // Mungkin tidak perlu diekspos langsung
            // 'notifiable_type' => class_basename($this->notifiable_type), // Hanya nama kelas
            'title' => $this->title,
            'message' => $this->message,
            'read_at' => $this->read_at ? $this->read_at->toDateTimeString() : null,
            'created_at' => $this->created_at->toDateTimeString(),
            'human_readable_created_at' => $this->created_at->diffForHumans(),
            // Anda bisa menambahkan data terkait dari notifiable jika diperlukan
            // 'related_link' => $this->generateRelatedLink(), // Contoh method custom di model Notification
        ];
    }
}