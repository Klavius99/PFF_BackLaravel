<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RequestLogging
{
    public function handle(Request $request, Closure $next)
    {
        // Log the incoming request
        Log::info('Incoming request', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'headers' => $request->headers->all(),
            'token' => $request->bearerToken() ? 'Present' : 'Absent'
        ]);

        // Process the request
        $response = $next($request);

        // Log the response
        Log::info('Outgoing response', [
            'status' => $response->status(),
            'content' => $response->content()
        ]);

        return $response;
    }
}
