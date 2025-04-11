<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class ValidateApiKey
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $token = $request->header('Authorization');
            if ($token) {
                $token = str_replace('Bearer ', '', $token);
            }
            Log::info($token);
            if (!$token) {
                return response()->json(['message' => 'Token not provided'], 401);
            }
            
            if ($token !== config('app.api_key')) {
                return response()->json(['message' => 'Invalid token'], 401);
            }

            return $next($request);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid token'], 401);
        }
    }
} 