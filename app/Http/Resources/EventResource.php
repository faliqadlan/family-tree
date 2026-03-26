<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'creator_id' => $this->creator_id,
            'name' => $this->name,
            'description' => $this->description,
            'location' => $this->location,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'status' => $this->status,
            'ancestor_node_id' => $this->ancestor_node_id,
            'invitation_depth' => $this->invitation_depth,
            'creator' => UserResource::make($this->whenLoaded('creator')),
            'committees' => $this->whenLoaded('committees'),
            'rsvps' => RsvpResource::collection($this->whenLoaded('rsvps')),
            'financial_contributions' => FinancialContributionResource::collection($this->whenLoaded('financialContributions')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
