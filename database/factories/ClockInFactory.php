<?php

namespace Database\Factories;

use App\Models\ClockIn;
use App\Models\Worker;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClockIn>
 */
class ClockInFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = ClockIn::class;

    public function definition(): array
    {
        return [
            'worker_id' => Worker::factory(),
            'latitude' => $this->faker->latitude($min = 40.7484, $max = 40.7492),
            'longitude' => $this->faker->longitude($min = -73.9865, $max = -73.9845),
            'timestamp' => Carbon::now()->timestamp,
        ];
    }
}
