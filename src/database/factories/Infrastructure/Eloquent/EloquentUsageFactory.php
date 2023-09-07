<?php

namespace Database\Factories\Infrastructure\Eloquent;

use App\Infrastructure\Eloquent\EloquentUsage;
use Illuminate\Database\Eloquent\Factories\Factory;

class EloquentUsageFactory extends Factory
{
    protected $model = EloquentUsage::class;

    public function definition()
    {
        return [
            'service_id' => function () {
                return \App\Infrastructure\Eloquent\EloquentService::factory()->create()->id;
            },
            'user_id' => function () {
                return \App\Infrastructure\Eloquent\EloquentUser::factory()->create()->id;
            },
            'usage_count' => $this->faker->randomNumber(),
            'actual_fee' => $this->faker->randomFloat(2, 10, 100),
            'month' => $this->faker->month(),
            'year' => $this->faker->year(),
            'created_at' => $this->faker->dateTime(),
        ];
    }
}