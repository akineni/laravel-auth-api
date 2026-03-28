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
        $data = is_array($this->data) ? $this->data : [];
        $notifiable = $this->whenLoaded('notifiable');

        return [
            'id' => $this->id,
            'notifiable' => [
                'id' => $this->notifiable_id,
                'type' => $this->mapNotifiableType($this->notifiable_type),
                'fullname' => $this->resolveNotifiableAttribute($notifiable, 'fullname'),
                'firstname' => $this->resolveNotifiableAttribute($notifiable, 'firstname'),
                'lastname' => $this->resolveNotifiableAttribute($notifiable, 'lastname'),
                'email' => $this->resolveNotifiableAttribute($notifiable, 'email'),
                'avatar' => $this->resolveNotifiableAttribute($notifiable, 'avatar'),
                'phone_number' => $this->resolveNotifiableAttribute($notifiable, 'phone_number'),
                'status' => $this->resolveNotifiableAttribute($notifiable, 'status'),
            ],
            'type' => $data['type'] ?? class_basename($this->type),
            'title' => $data['title'] ?? null,
            'message' => $data['message'] ?? null,
            'severity' => $data['severity'] ?? 'info',
            'action_url' => $data['action_url'] ?? null,
            'meta' => $data['meta'] ?? [],
            'read_at' => optional($this->read_at)?->toDateTimeString(),
            'created_at' => optional($this->created_at)?->toDateTimeString(),
        ];
    }

    protected function mapNotifiableType(?string $type): ?string
    {
        if (blank($type)) {
            return null;
        }

        return strtolower(class_basename($type));
    }

    protected function resolveNotifiableAttribute(mixed $notifiable, string $attribute): mixed
    {
        if (! is_object($notifiable)) {
            return null;
        }

        return data_get($notifiable, $attribute);
    }
}
