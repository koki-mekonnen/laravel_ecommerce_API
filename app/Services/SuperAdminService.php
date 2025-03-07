<?php

namespace App\Services;

use App\Repositories\MerchantRepository;

use App\Repositories\SuperAdminRepository;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class SuperAdminService
{
    protected $repository;

    public function __construct(SuperAdminRepository $repository)
    {
        $this->repository = $repository;
        $this->merchantRepository = new MerchantRepository();



    }

    public function registerSuperAdmin(array $data)
    {
        try {

            $data['password'] = Hash::make($data['password']);
            $data['role'] = 'superadmin';

            return $this->repository->create($data);

        } catch (\Exception $e) {
            \Log::error('Error registering admin: ' . $e->getMessage());

            // Re-throw the exception to be caught in the controller
            throw $e;
        }

    }

    public function authenticateSuperAdmin($email, $password, $role)
    {
        $admin = $this->repository->findByEmailAndRole($email, $role);

        if (!$admin) {
            \Log::info('Admin not found for email: ' . $email . ' and role: ' . $role);
            return null;
        }

        if (Hash::check($password, $admin->password)) {
            $token = JWTAuth::fromUser($admin);
            return [
                'admin' => $admin,
                'token' => $token,
            ];
        }

        \Log::info('Password mismatch for email: ' . $email);
        return null;
    }

// public function getAuthenticatedSuperAdmin($token)
// {
//     $admin = JWTAuth::setToken($token)->authenticate();

//     if (!$admin) {
//         return response()->json(['message' => 'SuperAdmin not found'], 404);
//     }

//     \Log::info('Authenticated SuperAdmin from the service: ' . json_encode($admin));

//     return $admin;
// }

    public function getAuthenticatedSuperAdmin($token)
    {
        try {
            $payload = JWTAuth::setToken($token)->getPayload();
            $userId = $payload->get('sub'); // Assuming 'sub' contains the user ID
            \Log::info('Looking for user with ID: ' . $userId . ' and Role: superadmin');

           $superadmin = $this->repository->findById( $userId);


            if (!$superadmin) {
                \Log::error('SuperAdmin not found with ID: ' . $userId);
                return null; // Return null if the SuperAdmin is not found
            }

            return $superadmin;
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            \Log::error('Token expired: ' . $e->getMessage());
            throw $e;
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            \Log::error('Invalid token: ' . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Unexpected error in token validation: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateSuperAdmin($id, array $data)
    {
        try {
            \Log::info('Updating superadmin ID: ' . $id . ' with data: ', $data);
            $superadmin = $this->repository->findById($id);
            if (!$superadmin) {
                \Log::info('SuperAdmin not found with ID: ' . $id);
return response()->json(['message' => 'SuperAdmin not found'], 404);


            }
            $superadmin->update($data);
            \Log::info('SuperAdmin updated successfully: ', ['id' => $superadmin->id]);
            return $superadmin;
        } catch (\Exception $e) {
            \Log::error('Error updating superadmin: ' . $id . ' with data: ' . $e->getMessage());
            throw $e;
        }

    }

    public function logout($token)
    {
        return JWTAuth::invalidate($token);
    }

    public function deleteMerchant($id, $merchantId)
    {
        try {
            // Find the superadmin by ID
            $superadmin = $this->repository->findById($id);

            // Log the IDs for debugging
            \Log::info('Processing delete request', ['superadminId' => $id, 'merchantId' => $merchantId]);

            if (!$superadmin) {
                \Log::info('SuperAdmin not found', ['superadminId' => $id]);
                return response()->json(['message' => 'SuperAdmin not found'], 404);
            }

            // Find the merchant by ID
            $merchant = $this->merchantRepository->findById($merchantId);

            if (!$merchant) {
                \Log::info('Merchant not found', ['merchantId' => $merchantId]);
                return response()->json(['message' => 'Merchant not found'], 404);
            }

            // Delete the merchant
            $deleted = $this->repository->deleteMerchant($merchantId);

            if ($deleted) {
                \Log::info('Merchant deleted successfully', [
                    'superadminId' => $id,
                    'merchantId' => $merchantId,
                ]);
                return response()->json(['message' => 'Merchant deleted successfully'], 200);
            } else {
                \Log::error('Failed to delete merchant', [
                    'superadminId' => $id,
                    'merchantId' => $merchantId,
                ]);
                return response()->json(['message' => 'Failed to delete merchant'], 500);
            }
        } catch (\Exception $e) {
            \Log::error('An error occurred while deleting merchant', [
                'superadminId' => $id,
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
