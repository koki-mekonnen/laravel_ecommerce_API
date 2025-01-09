<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;


class EnsureTokenIsValid
{
    public function handle(Request $request, Closure $next)
    {
// Check if the token is provided
$token = $request->bearerToken();
if (!$token) {
    return response()->json([
        'success' => false,
        'message' => 'Token not provided',
    ], 400); // 400 Bad Request
}

        try {
// Try to parse and verify the token
$payload = JWTAuth::parseToken()->getPayload();


            $role = $payload->get('role');
            $userId = $payload->get('sub');
Log::info("Looking for user with ID: $userId and Role: $role");




// Authenticate user based on role
$user = match ($role) {
    'superadmin' => auth('superadmin')->user(),
    'merchant' => auth('merchant')->user(),
    default => auth('api')->user(),
};


            if (!$user) {
return response()->json([
    'success' => false,
    'message' => ucfirst($role) . ' user not found',
], 404); // 404 Not Found

            }

Log::info('Authenticated User: ', [$user]);


        } catch (TokenExpiredException $e) {
            Log::error('TokenExpiredException: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Token has expired',
            ], 401); // 401 Unauthorized
        } catch (TokenInvalidException $e) {
            Log::error('TokenInvalidException: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Token is invalid',
            ], 401); // 401 Unauthorized
        } catch (JWTException $e) {
            Log::error('JWTException: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Token is invalid or could not be parsed',
            ], 401); // 401 Unauthorized
        } catch (\Exception $e) {
Log::error('Unexpected Exception: ' . $e->getMessage());
return response()->json([
    'success' => false,
    'message' => $e->getMessage(),
], 500); // 500 Internal Server Error

        }

// if (auth()->guest()) {
//     return response()->json([
//         'message' => 'Unauthorized access. Please log in.',
//     ], 401); // HTTP status code 401 for unauthorized
// }

        return $next($request);
    }
}
