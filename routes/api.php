<?php

use App\Http\Controllers\ClockInController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::middleware('user.auth')->prefix('worker')->group(function () {
    Route::post('/clock-in', [ClockInController::class, 'clockIn']);
    Route::get('/clock-ins/{workerId}', [ClockInController::class, 'getClockIns']);
});

