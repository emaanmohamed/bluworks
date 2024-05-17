<?php

namespace App\Services;

use App\Models\ClockIn;
use App\Models\Worker;
use Carbon\Carbon;
use GuzzleHttp\Client;

class ClockInService
{

    /* @param float $latitude
     * @param float $longitude
     * @return bool
     * @throws \Exception
     * */

    public function checkClockInAvailability($latitude, $longitude): bool
    {
        try {
            $validLatitude = 40.748817;
            $validLongitude = -73.985428;
            $maxDistance = 2;
            $distance = $this->calculateDistance($latitude, $longitude, $validLatitude, $validLongitude);
            return $distance <= $maxDistance;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    /**
     * Check if a worker is clocked in.
     *
     * @param int $workerId
     * @return bool
     */
    public function checkIfWorkerIsClockedIn($workerId)
    {
        $clockIn = ClockIn::where('worker_id', $workerId)->latest()->first();
        return Carbon::createFromTimestamp($clockIn->timestamp)->format('Y-m-d') == Carbon::now()->format('Y-m-d');
    }

    /**
     * Calculate the distance between two coordinates.
     * Using the Haversine formula a = sin²(Δφ/2) + cos φ1 ⋅ cos φ2 ⋅ sin²(Δλ/2)
     * c = 2 ⋅ atan2( √a, √(1−a) )
     * d = R ⋅ c
     *
     *
     * @param float $latitude1
     * @param float $longitude1
     * @param float $latitude2
     * @param float $longitude2
     * @return float
     */

    private function calculateDistance($latitude1, $longitude1, $latitude2, $longitude2)
    {
        $earthRadius = 6371;
        $latitudeDifference = deg2rad($latitude2 - $latitude1);
        $longitudeDifference = deg2rad($longitude2 - $longitude1);
        $a = sin($latitudeDifference / 2) * sin($latitudeDifference / 2) +
            cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) *
            sin($longitudeDifference / 2) * sin($longitudeDifference / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Clock in a worker.
     *
     * @param int $workerId
     * @param float $latitude
     * @param float $longitude
     * @param int $clockIn
     * @return ClockIn|bool
     */

    public function clockIn($workerId, $latitude, $longitude, $clockIn)
    {
       $clockIn = ClockIn::create([
           'worker_id' => $workerId,
           'latitude' => $latitude,
           'longitude' => $longitude,
           'timestamp' => $clockIn
       ]);
     return $clockIn ?: false ;
    }


    /**
     * Get clock-ins for a worker.
     *
     * @param int $workerId
     * @return ClockIn[]|null
     */
    public function getClockIns($workerId)
    {
        $clockIns = ClockIn::where('worker_id', $workerId)->get();
        return $clockIns;
    }








}