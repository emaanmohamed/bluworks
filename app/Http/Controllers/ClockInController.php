<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClockInResource;
use App\Services\ClockInService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Info(
 *     title="Bluworks API",
 *     version="1.0.0",
 *     description="API documentation for Bluworks",
 *     @OA\Contact(
 *         email="support@bluworks.com"
 *     )
 * )
 */
class ClockInController extends Controller
{
    private $clockInService;

    public function __construct(ClockInService $clockInService)
    {
        $this->clockInService = $clockInService;
    }

    /**
     *
     * @param Request $request The HTTP request object containing the clock-in details: "latitude", "longitude", "timestamp"
     * worker_id appended to the request object using middleware user.auth, I expect that user login in and using his own token to request this endpoint.
     * also I added tokens to workers table for testing perspective but best practise to use like passport or JWT for authentication.0
     * @return \Illuminate\Http\JsonResponse The response with the clock-in result.
     *
     * @throws \Exception If an error occurs during the clock-in process.
     *
     * @OA\Post(
     *     path="/worker/clock-in",
     *     summary="Clock in a worker",
     *     tags={"ClockIn"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"worker_id", "latitude", "longitude", "timestamp"},
     *             @OA\Property(property="worker_id", type="integer", example=1),
     *             @OA\Property(property="latitude", type="number", format="float", example=40.748817),
     *             @OA\Property(property="longitude", type="number", format="float", example=-73.985428),
     *             @OA\Property(property="timestamp", type="string", format="integer", example=1715904000),
     *         )
     *     ),
     *     @OA\Response(response=201, description="Clock in successful"),
     *     @OA\Response(response=400, description="Invalid input"),
     *     @OA\Response(response=403, description="Clock in location not within valid range"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function clockIn(Request $request)
    {
        try {
            $validator = $this->validator($request->all());
            if ($validator->fails()) {
                return ApiResponseMessageWithErrors("one or more fields validation required", $validator->errors(), 400);
            }

            $clockInAvailability = $this->clockInService->checkClockInAvailability($request->latitude, $request->longitude);
            if (!$clockInAvailability) {
                return ApiResponseMessage("You are not in the right location to clock in", 400);
            }
            $isClockedInForToday = $this->clockInService->checkIfWorkerIsClockedIn($request->worker_id);
            if ($isClockedInForToday) {
                return ApiResponseMessage("You have already clocked in for today", 400);
            }

            $clockIn = $this->clockInService->clockIn($request->worker_id, $request->latitude, $request->longitude, $request->timestamp);
            if (!$clockIn) {
                return ApiResponseMessage("an error occurred while clocking in", 400);
            }
            return ApiResponseDataWithMessage(new ClockInResource($clockIn), "clock in successful", 201);

        } catch (\Exception $e) {
            return ApiResponseMessage($e->getMessage(), 500);
        }
    }

    /**
     *
     * * Get clock-ins for a worker.
     *
     * @param Request $request The HTTP request object.
     * @param int $workerId The ID of the worker whose clock-ins are to be retrieved.
     * @return \Illuminate\Http\JsonResponse The response with the list of clock-ins.
     *
     * @throws \Exception If an error occurs during the retrieval process.
     *
     * @OA\Get(
     *     path="/worker/clock-ins",
     *     summary="Get clock-ins for a worker",
     *     tags={"ClockIn"},
     *     @OA\Parameter(
     *         name="worker_id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         example=1
     *     ),
     *     @OA\Response(response=200, description="Success"),
     *     @OA\Response(response=400, description="Invalid input"),
     *     @OA\Response(response=404, description="No clock-ins found")
     * )
     *
     *  * @OA\SecurityScheme(
     *     securityScheme="sanctum",
     *     type="http",
     *     scheme="bearer",
     *     bearerFormat="JWT",
     * )
     *
     */

    public function getClockIns(Request $request, $workerId)
    {
        try {
            if ($request->worker_id != $workerId) {
                return ApiResponseMessage("You are not authorized to view this worker's clock ins", 401);
            }
            $clockIns = $this->clockInService->getClockIns($workerId);
            if ($clockIns->isEmpty()) {
                return ApiResponseMessage("No clock-ins found", 404);
            }
            return ApiResponseData($clockIns);
        } catch (Exception $e) {
            return ApiResponseMessage($e->getMessage(), 500);

        }
    }

    protected function validator(array $data)
    {
        return Validator::make($data, $this->roles());
    }

    private function roles()
    {
        $roles = [
            'worker_id' => 'required|integer|exists:workers,id',
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'timestamp' => 'required|integer',
        ];
        return $roles;
    }
}
