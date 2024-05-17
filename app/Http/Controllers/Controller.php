<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Attributes as OA;


class Controller extends BaseController
{
    /**
     * @OA\Info(
     *     title="Bluworks API",
     *     version="1.0.0",
     *     description="API documentation for Bluworks",
     *     @OA\Contact(
     *         email="support@bluworks.com"
     *     )
     * )
     *
     *  * @OA\SecurityScheme(
     *     securityScheme="sanctum",
     *     type="http",
     *     scheme="bearer",
     *     bearerFormat="JWT",
     * )
     */
    use AuthorizesRequests, ValidatesRequests;
}
