<?php

namespace Database\Factories;

use App\Models\AccessRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccessRequestFactory extends Factory
{
    protected $model = AccessRequest::class;

    public function definition(): array
    {
        return [
            'requester_id'     => User::factory(),
            'target_id'        => User::factory(),
            'requested_fields' => ['phone'],
            'status'           => 'pending',
            'requester_message'=> $this->faker->sentence(),
        ];
    }
}
