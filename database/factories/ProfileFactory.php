<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProfileFactory extends Factory
{
    protected $model = Profile::class;

    public function definition(): array
    {
        return [
            'user_id'         => User::factory(),
            'full_name'       => $this->faker->name(),
            'nickname'        => $this->faker->firstName(),
            'gender'          => $this->faker->randomElement(['male', 'female']),
            'date_of_birth'   => $this->faker->date(),
            'place_of_birth'  => $this->faker->city(),
            'phone'           => $this->faker->phoneNumber(),
            'phone_privacy'   => 'masked',
            'email_privacy'   => 'masked',
            'dob_privacy'     => 'public',
            'address'         => $this->faker->address(),
            'address_privacy' => 'masked',
            'graph_node_id'   => (string) Str::uuid(),
        ];
    }
}
