<?php

namespace Tests\Feature;

use App\Models\ClockIn;
use App\Models\Worker;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClockInTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a worker for testing
        $this->worker = Worker::factory()->create();
        $this->token = $this->worker->createToken('auth_token')->plainTextToken;
        $this->worker->token = $this->token;
        $this->worker->save();
    }

    /** @test */
    public function it_can_clock_in_worker_within_valid_location()
    {
        $payload = [
            'worker_id' => $this->worker->id, // worker_id is appended to the request object using middleware user.auth
            'latitude' => 40.748817, // valid latitude within 2km
            'longitude' => -73.985428, // valid longitude within 2km
            'timestamp' => Carbon::now()->timestamp,
        ];

        $response = $this->postJson('/api/worker/clock-in', $payload, [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => [
                'id',
                'worker_id',
                'latitude',
                'longitude',
                'timestamp',
                'created_at',
                'updated_at'
            ]]);

        $this->assertDatabaseHas('clock_ins', [
            'worker_id' => $this->worker->id,
            'timestamp' => $payload['timestamp'],
            'latitude' => $payload['latitude'],
            'longitude' => $payload['longitude'],
        ]);
    }

    /** @test */
    public function it_fails_to_clock_in_worker_outside_valid_location()
    {
        $payload = [
            'latitude' => 30.748817, // invalid latitude outside 2km
            'longitude' => -73.985428, // invalid longitude outside 2km
            'timestamp' => Carbon::now()->timestamp,
        ];

        $response = $this->postJson('/api/worker/clock-in', $payload, [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(400)
            ->assertJson(['message' => 'You are not in the right location to clock in']);
    }

    /** @test */
    public function it_requires_authentication_to_clock_in()
    {
        $payload = [
            'latitude' => 40.748817,
            'longitude' => -73.985428,
            'timestamp' => Carbon::now()->timestamp,
        ];

        $response = $this->postJson('/api/worker/clock-in', $payload);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Token not found']);
    }

    /** @test */
    public function it_can_retrieve_worker_clock_ins()
    {
        $clockIn1 = ClockIn::factory()->create([
            'worker_id' => $this->worker->id,
            'latitude' => 40.748817,
            'longitude' => -73.985428,
            'timestamp' => Carbon::now()->timestamp,
        ]);

        $clockIn2 = ClockIn::factory()->create([
            'worker_id' => $this->worker->id,
            'latitude' => 40.748817,
            'longitude' => -73.985428,
            'timestamp' => Carbon::now()->timestamp,
        ]);

        $response = $this->getJson('/api/worker/clock-ins/' . $this->worker->id, [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure(['data' => [
                '*' => [
                    'id',
                    'worker_id',
                    'timestamp',
                    'latitude',
                    'longitude',
                    'created_at',
                    'updated_at'
                ]
            ]]);
    }

}
