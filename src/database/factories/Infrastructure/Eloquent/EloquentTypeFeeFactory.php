<?php

namespace Database\Factories\Infrastructure\Eloquent;

use App\Infrastructure\Eloquent\EloquentTypeFee;
use Illuminate\Database\Eloquent\Factories\Factory;

class EloquentTypeFeeFactory extends Factory
{
    protected $model = EloquentTypeFee::class;

    public function definition()
    {
        return [
            'type' => $this->faker->word,
            'description' => $this->faker->sentence,
        ];
    }
}