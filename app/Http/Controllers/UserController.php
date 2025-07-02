<?php

namespace App\Http\Controllers;
use App\Services\UserService;
use App\Http\Requests\UserRequest;
use Illuminate\Http\Request;

class UserController extends Controller
{
     protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        $users = User::all();
        return response()->json(['data' => $users], 200);
    }

    public function register(UserRequest $request)
    {

        try {
            $user = $this->userService->registerUser($request->validated());

            if (! $user) {
                \Log::error('User registration failed.');
                return response()->json(['message' => 'Registration failed'], 500);
            }

            \Log::info('User registered successfully.', ['id' => $user->id]);
            return response()->json([
                'message' => 'User registered successfully',
                'data'    => $user,
            ], 201);
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error during user registration: ' . $e->getMessage());
            return response()->json([
                'error'   => 'Database error',
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            \Log::error('Error during user registration: ' . $e->getMessage());
            return response()->json([
                'error'   => 'An unexpected error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }

    }

    public function login(UserRequest $request)
    {
        try {
            $validated = $request->validated();

            $phone    = $validated['phone'];
            $password = $validated['password'];
            $role     = $validated['role'];

            $result = $this->userService->authenticateUser($phone, $password, $role);

            if (isset($result['error'])) {
                return response()->json(['message' => $result['error']], $result['code']);
            }

            $user = $result['user'];
            $token    = $result['token'];

            return response()->json([
                'message' => 'Logged in successfully',
                'token'   => $token,
                'data'    => [
                    'id'        => $user->id,
                    'firstname' => $user->firstname,
                    'lastname'  => $user->lastname,
                    'phone'     => $user->phone,
                    'email'     => $user->email,
                    'role'      => $user->role,
                ],
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error during login: ' . $e->getMessage());

            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }

    public function user(Request $request)
    {
        try {
            $token = $request->bearerToken();

            if (! $token) {
                \Log::error('Token not provided');

                return response()->json(['message' => 'Token not provided'], 400);
            }

            $user = $this->userService->getAuthenticatedUser($token);

            if (! $user) {
                \Log::error('User not found after token validation');

                return response()->json(['message' => 'User not found'], 404);
            }

            \Log::info('User successfully retrieved', ['id' => $user->id]);

            return response()->json([
                'message' => 'User retrieved successfully',
                'data'    => [
                    'id'        => $user->id,
                    'firstname' => $user->firstname,
                    'lastname'  => $user->lastname,
                    'phone'     => $user->phone,
                    'email'     => $user->email,
                    'role'      => $user->role,
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

            if (! $token) {
                return response()->json(['message' => 'Token not provided'], 400);
            }

            $this->userService->logout($token);

            return response()->json(['message' => 'Successfully logged out'], 200);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Failed to logout', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $token = $request->bearerToken();

            if (! $token) {
                return response()->json(['message' => 'Token not provided'], 400);
            }

            $user = $this->userService->getAuthenticatedUser($token);

            if (! $user) {
                return response()->json(['message' => 'Authentication failed or user not found'], 401);
            }

            $validatedData = $request->validate([
                'firstname' => 'nullable|string|max:255',
                'lastname'  => 'nullable|string|max:255',
                'phone'     => 'nullable|string|max:255',
                'email'     => 'nullable|string|email|max:255',
               'password' => 'nullable|string|max:255',
            ]);

            $updatedUser = $this->userService->updateUser($user['id'], $validatedData);
            return response()->json([
                'message' => 'User updated successfully',
                'data'    => $updatedUser,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error while updating user: ' . $e->getMessage());
            return response()->json([
                'message' => 'Validation error',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('An error occurred while updating the user: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while updating the user',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


  public function getMerchantsByCategoryName(Request $request)
{
    try {

        $validatedData = $request->validate([
        'category_name' => 'nullable|string|max:255' ]);

        $categoryName =  $validatedData['category_name'];
        $merchants = $this->userService->getMerchantsByCategoryName($categoryName);

        if ($merchants->isEmpty()) {
            return response()->json(['message' => 'No merchants found for this category'], 404);
        }

        return response()->json([
            'message' => 'Merchants retrieved successfully',
            'data' => $merchants,
        ], 200);

    } catch (\Exception $e) {
        \Log::error('Error retrieving merchants by category: ' . $e->getMessage());
        return response()->json([
            'message' => 'An error occurred',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function getAllCategories()
{
    try {
        $categories = $this->userService->getAllCategory();

        if ($categories->isEmpty()) {
            return response()->json(['message' => 'No categories found'], 404);
        }

        return response()->json([
            'message' => 'Categories retrieved successfully',
            'data' => $categories,
        ], 200);
    } catch (\Exception $e) {
        \Log::error('Error retrieving categories: ' . $e->getMessage());
        return response()->json([
            'message' => 'An error occurred',
            'error' => $e->getMessage()
        ], 500);
    }

}
}

