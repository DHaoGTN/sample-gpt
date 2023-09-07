<?php

namespace Database\Factories\Infrastructure\Eloquent;

use App\Infrastructure\Eloquent\EloquentService;
use Illuminate\Database\Eloquent\Factories\Factory;

class EloquentServiceFactory extends Factory
{
    protected $model = EloquentService::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'type_fee_id' => function () {
                return \App\Infrastructure\Eloquent\EloquentTypeFee::factory()->create()->id;
            },
            'price' => $this->faker->randomFloat(2, 10, 100),
            'created_at' => $this->faker->dateTime(),
        ];
    }
}