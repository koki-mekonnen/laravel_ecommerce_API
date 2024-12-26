<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\SuperAdminService;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;

class SuperAdminController extends Controller
{
    protected $superAdminService;

    public function __construct(SuperAdminService $superAdminService)
    {
        $this->superAdminService = $superAdminService;
    }

    public function register(Request $request)
    {
        $request->validate([
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'phone' => 'required|string|max:13',
            'email' => 'required|string|email|unique:super_admins,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:merchant,superadmin,user',
        ]);

        try {
            $admin = $this->superAdminService->registerSuperAdmin($request->all());
            return response()->json([
                'message' => 'SuperAdmin registered successfully',
                'data' => $admin,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Registration failed: ' . $e->getMessage()], 500);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'role' => 'required|string|in:superadmin',
        ]);

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
