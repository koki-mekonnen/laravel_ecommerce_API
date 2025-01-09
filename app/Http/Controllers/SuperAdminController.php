<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdminRequest;
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
                \Log::error('Token not provided');

                return response()->json(['message' => 'Token not provided'], 400);
            }

            $admin = $this->superAdminService->getAuthenticatedSuperAdmin($token);

            if (!$admin) {
                \Log::error('SuperAdmin not found after token validation');
                return response()->json(['message' => 'SuperAdmin not found'], 404);
            }

            \Log::info('SuperAdmin successfully retrieved', ['id' => $admin->id]);

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
            \Log::error('Token expired: ' . $e->getMessage());

            return response()->json(['message' => 'Token has expired', 'error' => $e->getMessage()], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            \Log::error('Invalid token: ' . $e->getMessage());

            return response()->json(['message' => 'Invalid token', 'error' => $e->getMessage()], 401);

        } catch (\Exception $e) {
            \Log::error('Unexpected error: ' . $e->getMessage());
            return response()->json(['message' => 'Unexpected error occurred'], 500);
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

    public function update(SuperAdminRequest $request)
    {
        try {
            $token = $request->bearerToken();

            if (!$token) {
                return response()->json(['message' => 'Token not provided'], 400);
            }

            $admin = $this->superAdminService->getAuthenticatedSuperAdmin($token);

            if (!$admin) {
                return response()->json(['message' => 'Authentication failed or admin not found'], 401);
            }

            // Validate incoming request data
            $validatedData = $request->validate([
                'firstname' => 'nullable|string|max:255',
                'lastname' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:255',
                'email' => 'nullable|string|email|max:255',

            ]);

            // Update admin details
            $updatedSuperAdmin = $this->superAdminService->updateSuperAdmin($admin['id'], $validatedData);

            return response()->json([
                'message' => 'SuperAdmin updated successfully',
                'data' => $updatedSuperAdmin,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error while updating admin: ' . $e->getMessage());
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('An error occurred while updating the admin: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while updating the admin',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteMerchant(Request $request, $merchantId)
    {
        try {
            $token = $request->bearerToken();

            if (!$token) {
                return response()->json(['message' => 'Token not provided'], 400);
            }

            // Log the merchant ID properly
            \Log::info('Deleting merchant', ['merchantId' => $merchantId]);

            // Authenticate the super admin using the provided token
            $admin = $this->superAdminService->getAuthenticatedSuperAdmin($token);

            if (!$admin) {
                return response()->json(['message' => 'Authentication failed or admin not found'], 401);
            }

            // Call the service to delete the merchant using the merchantId
            $deleted = $this->superAdminService->deleteMerchant($admin['id'], $merchantId);

            if ($deleted) {
                return response()->json([
                    'message' => 'Merchant deleted successfully',
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Failed to delete merchant',
                ], 500);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error while deleting merchant: ' . $e->getMessage());
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('An error occurred while deleting the merchant', [
                'merchantId' => $merchantId,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'An error occurred while deleting the merchant',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
