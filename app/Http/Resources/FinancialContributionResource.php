<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinancialContributionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event_id' => $this->event_id,
            'contributor_id' => $this->contributor_id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'reference_number' => $this->reference_number,
            'note' => $this->note,
            'confirmed_by' => $this->confirmed_by,
            'confirmed_at' => $this->confirmed_at,
            'event' => $this->whenLoaded('event', fn() => [
                'id' => $this->event->id,
                'name' => $this->event->name,
                'status' => $this->event->status,
            ]),
            'contributor' => UserResource::make($this->whenLoaded('contributor')),
            'confirmed_user' => UserResource::make($this->whenLoaded('confirmedBy')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
