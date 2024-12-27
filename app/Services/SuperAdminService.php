<?php

namespace App\Services;

use App\Repositories\SuperAdminRepository;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class SuperAdminService
{
    protected $repository;

    public function __construct(SuperAdminRepository $repository)
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

    public function getAuthenticatedSuperAdmin($token)
    {
        $admin = JWTAuth::setToken($token)->authenticate();

        if (!$admin) {
            return response()->json(['message' => 'SuperAdmin not found'], 404);
        }

        \Log::info('Authenticated SuperAdmin: ' . json_encode($admin));

        return $admin;
    }

    public function logout($token)
    {
        return JWTAuth::invalidate($token);
    }
}
