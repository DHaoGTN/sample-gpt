<?php

namespace Database\Factories\Infrastructure\Eloquent;

use App\Infrastructure\Eloquent\EloquentUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EloquentUserFactory extends Factory
{
    protected $model = EloquentUser::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => bcrypt(Str::random(10)),
            'phone_number' => $this->faker->phoneNumber,
        ];
    }
}
