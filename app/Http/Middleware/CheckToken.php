<?php

namespace App\Http\Middleware;

use App\Models\Worker;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('Authorization');
        if(!$token)
            return response()->json(['message' => 'Token not found'], 401);
        $token = str_replace('Bearer ', '', $token);
        $worker = Worker::where('token', $token)->first();
        if(!$worker)
            return response()->json(['message' => 'Unauthorized'], 401);
        $request->merge(['worker_id' => $worker->id]);
        return $next($request);
    }
}
