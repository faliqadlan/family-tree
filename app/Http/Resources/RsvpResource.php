<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RsvpResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event_id' => $this->event_id,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'pax' => $this->pax,
            'note' => $this->note,
            'event' => $this->whenLoaded('event', fn() => [
                'id' => $this->event->id,
                'name' => $this->event->name,
                'status' => $this->event->status,
            ]),
            'user' => UserResource::make($this->whenLoaded('user')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
