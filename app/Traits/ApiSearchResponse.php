<?php

namespace App\Traits;

trait ApiSearchResponse
{
    /**
     * Success Response
     */
    protected function successResponse($data, $message = null, $code = 200)
    {
        if (isset($data['data']) && isset($data['paging'])) {
            // If data already has the correct format, return it directly
            return response()->json($data, $code);
        }

        // Otherwise, wrap it in the standard format
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Error Response
     */
    protected function errorResponse($message, $code = 400)
    {
        $response = [
            'status' => 'error',
            'message' => 'Validation failed'
        ];

        if (is_array($message)) {
            $response['errors'] = $message;
        } else {
            $response['message'] = $message;
        }

        return response()->json($response, $code);
    }

    /**
     * Response with token
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60
            ]
        ], 200);
    }
} 