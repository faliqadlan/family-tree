<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if (is_array($this->resource)) {
            return $this->resource;
        }

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'full_name' => $this->full_name,
            'nickname' => $this->nickname,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth,
            'date_of_death' => $this->date_of_death,
            'place_of_birth' => $this->place_of_birth,
            'bio' => $this->bio,
            'phone' => $this->phone,
            'phone_privacy' => $this->phone_privacy,
            'email_privacy' => $this->email_privacy,
            'dob_privacy' => $this->dob_privacy,
            'address' => $this->address,
            'address_privacy' => $this->address_privacy,
            'father_name' => $this->father_name,
            'mother_name' => $this->mother_name,
            'graph_node_id' => $this->graph_node_id,
            'user' => UserResource::make($this->whenLoaded('user')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
