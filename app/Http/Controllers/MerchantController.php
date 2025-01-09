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

            $phone = $validated['phone'];
            $password = $validated['password'];
            $role = $validated['role'];

            $result = $this->merchantService->authenticateMerchant($phone, $password, $role);

            if (isset($result['error'])) {
                return response()->json(['message' => $result['error']], $result['code']);
            }

            $merchant = $result['merchant'];
            $token = $result['token'];

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

        } catch (\Exception $e) {
\Log::error('Error during login: ' . $e->getMessage());

            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }





       public function merchant(Request $request)
    {
        try {
            $token = $request->bearerToken();

            if (!$token) {
\Log::error('Token not provided');

                return response()->json(['message' => 'Token not provided'], 400);
            }

$merchant = $this->merchantService->getAuthenticatedMerchant($token);




            if (!$merchant) {
\Log::error('Merchant not found after token validation');

                return response()->json(['message' => 'Merchant not found'], 404);
            }

\Log::info('Merchant successfully retrieved', ['id' => $merchant->id]);

            return response()->json([
                'message' => 'Merchant retrieved successfully',
                'data' => [
                    'id' => $merchant->id,
                    'firstname' => $merchant->firstname,
                    'lastname' => $merchant->lastname,
                    'phone' => $merchant->phone,
                    'email' => $merchant->email,
                    'role' => $merchant->role,
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

            $this->merchantService->logout($token);

            return response()->json(['message' => 'Successfully logged out'], 200);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Failed to logout', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $token = $request->bearerToken();

            if (!$token) {
                return response()->json(['message' => 'Token not provided'], 400);
            }

            $merchant = $this->merchantService->getAuthenticatedMerchant($token);

            if (!$merchant) {
                return response()->json(['message' => 'Authentication failed or merchant not found'], 401);
            }

            // Validate incoming request data
            $validatedData = $request->validate([
                'firstname' => 'nullable|string|max:255',
                'lastname' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:255',
                'email' => 'nullable|string|email|max:255',
                'license' => 'nullable|string|max:255',
                'tinnumber' => 'nullable|string|max:255',
            ]);

            // Update merchant details
            $updatedMerchant = $this->merchantService->updateMerchant($merchant['id'], $validatedData);

            return response()->json([
                'message' => 'Merchant updated successfully',
                'data' => $updatedMerchant,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error while updating merchant: ' . $e->getMessage());
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('An error occurred while updating the merchant: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while updating the merchant',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
