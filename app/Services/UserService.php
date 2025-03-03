<?php
namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserService
{
    protected $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function registerUser(array $data)
    {


        try {
            $data['password'] = Hash::make($data['password']);
            $data['role']     = 'user';

            $user = $this->repository->create($data);

            \Log::info('User successfully created: ', ['id' => $user->id]);
            return $user;
        } catch (\Exception $e) {
            \Log::error('Error registering user: ' . $e->getMessage());

            // Re-throw the exception to be caught in the controller
            throw $e;
        }
    }

    public function authenticateUser($phone, $password, $role)
    {
        \Log::info('Authenticating user with phone: ' . $phone . ' and role: ' . $role);

        $user = $this->repository->findByPhoneAndRole($phone, $role);

        if (! $user) {
            \Log::info('User not found for phone: ' . $phone . ' and role: ' . $role);
            return [
                'error' => 'User not found with this phone',
                'code'  => 404,
            ];
        }

        if (! Hash::check($password, $user->password)) {
            \Log::info('Password mismatch for phone: ' . $phone);
            return [
                'error' => 'Invalid  password used ',
                'code'  => 401,
            ];
        }

        $token = JWTAuth::customClaims(['ttl' => config('jwt.refresh_ttl')])->fromUser($user);

        \Log::info('Authentication successful for user ID: ' . $user->id);

        return [
            'user' => $user,
            'token'    => $token,
        ];
    }

    public function getAuthenticatedUser($token)
    {
        try {
            $payload = JWTAuth::setToken($token)->getPayload();

            $userId = $payload->get('sub');
            \Log::info('Looking for user with ID: ' . $userId . ' and Role: user');

            $user = $this->repository->findById($userId);

            if (! $user) {
                \Log::error('User not found with ID: ' . $userId);
                return null;

            }

            return $user;
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



    public function updateUser($id, array $data)
    {
        try {
            \Log::info('Updating user ID: ' . $id . ' with data: ', $data);

            $user = $this->repository->findById($id);

            if (! $user) {
                \Log::info('User not found with ID: ' . $id);
                return response()->json(['message' => 'User not found'], 404);
            }
            $user = $this->repository->update($data,$id);

            return $user;
        } catch (\Exception $e) {
            \Log::error('Error updating user: ' . $id . ' with data: ' . $e->getMessage());
            throw $e;

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
