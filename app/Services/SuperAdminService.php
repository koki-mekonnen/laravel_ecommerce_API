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

    public function registerSuperAdmin(array $data)
    {
        $data['password'] = Hash::make($data['password']);
        $data['role'] = 'superadmin';

        return $this->repository->create($data);
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
