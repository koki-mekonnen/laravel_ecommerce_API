<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\SuperAdminService;
use App\Http\Requests\SuperAdminRequest;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Contracts\Validation\Validator;


class SuperAdminController extends Controller
{
    protected $superAdminService;

    public function __construct(SuperAdminService $superAdminService)
    {
        $this->superAdminService = $superAdminService;
    }

    public function register(SuperAdminRequest $request)
    {


        try {
            $admin = $this->superAdminService->registerSuperAdmin($request->all());

if (!$admin) {
    \Log::error('SuperAdmin registration failed.');
    return response()->json(['message' => 'Registration failed'], 500);
}

// Registration successful
\Log::info('SuperAdmin ID generated: ' . $admin->id);

            return response()->json([
                'message' => 'SuperAdmin registered successfully',
                'data' => $admin,
            ], 201);

        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database-related errors
            \Log::error('Database error during admin registration: ' . $e->getMessage());
            return response()->json([
                'error' => 'Database error',
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
// Handle general errors
\Log::error('Error during admin registration: ' . $e->getMessage());
return response()->json([
    'error' => 'An unexpected error occurred',
    'message' => $e->getMessage(),
], 500);

        }
    }

    public function login(SuperAdminRequest $request)
    {


        try {
            $result = $this->superAdminService->authenticateSuperAdmin(
                $request->email,
                $request->password,
                $request->role
            );

            if (!$result || !isset($result['token'], $result['admin'])) {
                \Log::info('Failed to generate token or retrieve admin for provided credentials.');
                return response()->json(['message' => 'Invalid credentials or role mismatch'], 401);
            }

            $admin = $result['admin'];
            $token = $result['token'];

            \Log::info('Token generated successfully: ' . $token);

            // Successful response with admin details
            return response()->json([
                'message' => 'Logged in successfully',
                'token' => $token,
                'data' => [
                    'id' => $admin->id,
                    'firstname' => $admin->firstname,
                    'lastname' => $admin->lastname,
                    'phone' => $admin->phone,
                    'email' => $admin->email,
                    'role' => $admin->role,
                ],
            ], 200);

        } catch (JWTException $e) {
            \Log::error('JWTException encountered: ' . $e->getMessage());
            return response()->json(['message' => 'Could not create token', 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            \Log::error('General exception encountered: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }

    public function admin(Request $request)
    {
        try {
            $token = $request->bearerToken();

            if (!$token) {
                return response()->json(['message' => 'Token not provided'], 400);
            }

            $admin = $this->superAdminService->getAuthenticatedSuperAdmin($token);

            if (!$admin) {
                return response()->json(['message' => 'Authentication failed or user not found'], 401);
            }

            if (!$admin) {
                return response()->json(['message' => 'SuperAdmin not found'], 404);
            }

            return response()->json([
                'message' => 'SuperAdmin retrieved successfully',
                'data' => [
                    'id' => $admin->id,
                    'firstname' => $admin->firstname,
                    'lastname' => $admin->lastname,
                    'phone' => $admin->phone,
                    'email' => $admin->email,
                    'role' => $admin->role,
                ],
            ], 200);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['message' => 'Token has expired', 'error' => $e->getMessage()], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['message' => 'Invalid token', 'error' => $e->getMessage()], 401);
        }
    }

    public function logout(Request $request)
    {
        try {
            $token = $request->bearerToken();

            if (!$token) {
                return response()->json(['message' => 'Token not provided'], 400);
            }

            $this->superAdminService->logout($token);

            return response()->json(['message' => 'Successfully logged out'], 200);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Failed to logout', 'error' => $e->getMessage()], 500);
        }
    }
}
