<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $payload = auth()->payload();

            $role = $payload->get('role');
            $userId = $payload->get('sub');

            \Log::info("Looking for user with ID: $userId and Role: $role");

            if ($role === 'superadmin') {
                $user = auth('superadmin')->user();
            } elseif ($role === 'merchant') {
                $user = auth('merchant')->user();
            } else {
                $user = auth('api')->user();
            }

            if (!$user) {
                return response()->json(['message' => ucfirst($role) . ' not found'], 404);
            }

            \Log::info('Authenticated User: ', [$user]);

        } catch (\Exception $e) {
            \Log::error('Exception in Middleware: ' . $e->getMessage());
            return response()->json(['message' => 'Token is invalid or expired'], 401);
        }

        return $next($request);
    }
}
