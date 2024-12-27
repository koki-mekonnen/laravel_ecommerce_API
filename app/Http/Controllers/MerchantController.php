<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterMerchantRequest;
use App\Models\Merchant;
use App\Services\MerchantService;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;

class MerchantController extends Controller
{
    protected $merchantService;

    public function __construct(MerchantService $merchantService)
    {
        $this->merchantService = $merchantService;
    }

    public function index()
    {
        $merchants = Merchant::all();
        return response()->json(['data' => $merchants], 200);
    }

    public function register(RegisterMerchantRequest $request)
    {

        try {
            $merchant = $this->merchantService->registerMerchant($request->validated());

            if (!$merchant) {
                \Log::error('Merchant registration failed.');
                return response()->json(['message' => 'Registration failed'], 500);
            }

            // Registration successful
            \Log::info('Merchant registered successfully.', ['id' => $merchant->id]);
            return response()->json([
                'message' => 'Merchant registered successfully',
                'data' => $merchant,
            ], 201);
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database-related errors
            \Log::error('Database error during merchant registration: ' . $e->getMessage());
            return response()->json([
                'error' => 'Database error',
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            // Handle general errors
            \Log::error('Error during merchant registration: ' . $e->getMessage());
            return response()->json([
                'error' => 'An unexpected error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }

    }

    public function login(RegisterMerchantRequest $request)
    {
        try {
            $validated = $request->validated();

            // Extract individual parameters
            $phone = $validated['phone'];
            $password = $validated['password'];
            $role = $validated['role'];

            // Call the service with extracted parameters
            $result = $this->merchantService->authenticateMerchant($phone, $password, $role);

            if (!$result || !isset($result['token'], $result['merchant'])) {
                \Log::info('Failed to generate token or retrieve merchant for provided credentials.');
                return response()->json(['message' => 'Invalid credentials or role mismatch'], 401);
            }

            $merchant = $result['merchant'];
            $token = $result['token'];

            \Log::info('Token generated successfully: ' . $token);

            return response()->json([
                'message' => 'Logged in successfully',
                'token' => $token,
                'data' => [
                    'id' => $merchant->id,
                    'firstname' => $merchant->firstname,
                    'lastname' => $merchant->lastname,
                    'phone' => $merchant->phone,
                    'email' => $merchant->email,
                    'license' => $merchant->license,
                    'tinnumber' => $merchant->tinnumber,
                    'role' => $merchant->role,
                ],
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            \Log::error('JWTException encountered: ' . $e->getMessage());
            return response()->json(['message' => 'Could not create token', 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            \Log::error('General exception encountered: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }

    public function merchant(Request $request)
    {
        try {
            $token = $request->bearerToken();

            if (!$token) {
                return response()->json(['message' => 'Token not provided'], 400);
            }

            $merchant = $this->merchantService->getAuthenticatedMerchant($token);

            if (!$merchant) {
                return response()->json(['message' => 'Authentication failed or user not found'], 401);
            }

            if (!$merchant) {
                return response()->json(['message' => 'Merchant not found'], 404);
            }

            return response()->json([
                'message' => 'Merchant retrieved successfully',
                'data' => [
                    'id' => $merchant->id,
                    'firstname' => $merchant->firstname,
                    'lastname' => $merchant->lastname,
                    'phone' => $merchant->phone,
                    'email' => $merchant->email,
                    'license' => $merchant->license,
                    'tinnumber' => $merchant->tinnumber,
                    'role' => $merchant->role,
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

            $this->merchantService->logout($token);

            return response()->json(['message' => 'Successfully logged out'], 200);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Failed to logout', 'error' => $e->getMessage()], 500);
        }
    }
}
