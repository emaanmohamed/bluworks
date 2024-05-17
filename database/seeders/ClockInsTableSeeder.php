<?php

namespace Database\Seeders;

use App\Models\ClockIn;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClockInsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ClockIn::factory()->count(50)->create();

    }
}
