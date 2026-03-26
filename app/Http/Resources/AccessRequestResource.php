<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccessRequestResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'requester_id' => $this->requester_id,
            'target_id' => $this->target_id,
            'requested_fields' => $this->requested_fields,
            'status' => $this->status,
            'requester_message' => $this->requester_message,
            'target_response' => $this->target_response,
            'responded_at' => $this->responded_at,
            'requester' => UserResource::make($this->whenLoaded('requester')),
            'target' => UserResource::make($this->whenLoaded('target')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
