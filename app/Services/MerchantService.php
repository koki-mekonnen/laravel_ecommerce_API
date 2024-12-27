<?php

namespace App\Services;

use App\Repositories\MerchantRepository;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class MerchantService
{
    protected $repository;

    public function __construct(MerchantRepository $repository)
    {
        $this->repository = $repository;
    }

    public function registerMerchant(array $data)
    {
        \Log::info('Register Merchant data received: ', $data); // Log incoming data

        try {
            $data['password'] = Hash::make($data['password']);
            $data['role'] = 'merchant';

            $merchant = $this->repository->create($data);

            \Log::info('Merchant successfully created: ', ['id' => $merchant->id]);
            return $merchant;
        } catch (\Exception $e) {
            \Log::error('Error registering merchant: ' . $e->getMessage());

            // Re-throw the exception to be caught in the controller
            throw $e;
        }
    }

    public function authenticateMerchant($phone, $password, $role)
    {
        \Log::info('Authenticating merchant with phone: ' . $phone . ' and role: ' . $role);

        $merchant = $this->repository->findByPhoneAndRole($phone, $role);

        if (!$merchant) {
            \Log::info('Merchant not found for phone: ' . $phone . ' and role: ' . $role);
            return null;
        }

        if (Hash::check($password, $merchant->password)) {
            $token = JWTAuth::fromUser($merchant);
            \Log::info('Authentication successful for merchant ID: ' . $merchant->id);
            return [
                'merchant' => $merchant,
                'token' => $token,
            ];
        }

        \Log::info('Password mismatch for phone: ' . $phone);
        return null;
    }

    public function getAuthenticatedMerchant($token)
    {
        try {
            \Log::info('Authenticating token: ' . $token);

            $merchant = JWTAuth::setToken($token)->authenticate();

            if (!$merchant) {
                \Log::info('Merchant not found for token: ' . $token);
                return response()->json(['message' => 'Merchant not found'], 404);
            }

            \Log::info('Authenticated Merchant: ', ['id' => $merchant->id]);
            return $merchant;
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            \Log::error('Token is invalid: ' . $e->getMessage());
            return response()->json(['message' => 'Token is invalid'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            \Log::error('Token has expired: ' . $e->getMessage());
            return response()->json(['message' => 'Token has expired'], 401);
        } catch (\Exception $e) {
            \Log::error('An error occurred while authenticating merchant: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred'], 500);
        }
    }

    public function logout($token)
    {
        try {
            \Log::info('Invalidating token: ' . $token);
            JWTAuth::invalidate($token);
            \Log::info('Token invalidated successfully.');
        } catch (\Exception $e) {
            \Log::error('Error invalidating token: ' . $e->getMessage());
        }
    }
}
