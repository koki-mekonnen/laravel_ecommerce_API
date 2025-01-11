<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Services\CategoryService;
use App\Services\MerchantService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{

    protected $merchantService;
    protected $categoryService;

    public function __construct(CategoryService $categoryService, MerchantService $merchantService)
    {
        $this->merchantService = $merchantService;
        $this->categoryService = $categoryService;
    }

    public function store(CategoryRequest $request)
    {
        try {
            $token = $request->bearerToken();

            if (!$token) {
                \Log::error('Token not provided');

                return response()->json(['message' => 'Token not provided'], 400);
            }

            $merchant = $this->merchantService->getAuthenticatedMerchant($token);

            if (!$merchant) {
                \Log::error('Merchant not found');

                return response()->json(['message' => 'Merchant not found'], 404);
            }

            $data = $request->validated(); // Get validated data
            $data['owner_id'] = $merchant->id; // Ensure owner_id is set to the authenticated merchant's ID

            \Log::info('data passed: ', $data);

            $category = $this->categoryService->createCategory($data);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => $category,
            ], 201);
        } catch (\Throwable $e) {
            \Log::error('Error during category creation: ' . $e->getMessage());
            return response()->json([
                'error' => 'An unexpected error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function index()
    {
        try {
            $token = request()->bearerToken();
            if (!$token) {
                \Log::error('Token not provided');
                return response()->json(['message' => 'Token not provided'], 400);
            }
            $merchant = $this->merchantService->getAuthenticatedMerchant($token);
            if (!$merchant) {
                \Log::error('Merchant not found');
                return response()->json(['message' => 'Merchant not found'], 404);
            }
            $categories = $this->categoryService->getAllCategories();

            return response()->json([
                'success' => true,
                'message' => 'Categories retrieved successfully',
                'data' => $categories,
            ], 200);
        } catch (\Throwable $e) {
            \Log::error('Error during category retrieval: ' . $e->getMessage());
            return response()->json([
                'error' => 'An unexpected error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getByCategoryName(Request $request)
    {
        try {
            $token = $request->bearerToken();

            if (!$token) {
                \Log::error('Token not provided');
                return response()->json(['message' => 'Token not provided'], 400);
            }

            $merchant = $this->merchantService->getAuthenticatedMerchant($token);
            if (!$merchant) {
                \Log::error('Merchant not found');
                return response()->json(['message' => 'Merchant not found'], 404);
            }

            // Retrieve category_name from the query parameter
            $categoryName = $request->query('category_name');

            if (!$categoryName) {
                \Log::error('Category name not provided');
                return response()->json(['message' => 'Category name not provided'], 400);
            }

            $categories = $this->categoryService->getByCategoryName($categoryName, $merchant->id);

            return response()->json([
                'success' => true,
                'message' => 'Categories retrieved successfully',
                'data' => $categories,
            ], 200);
        } catch (\Throwable $th) {
            \Log::error('Error retrieving categories: ' . $th->getMessage());
            return response()->json([
                'error' => 'An unexpected error occurred',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function getByCategoryType(Request $request)
    {
        try {
            $token = $request->bearerToken();

            if (!$token) {
                \Log::error('Token not provided');
                return response()->json(['message' => 'Token not provided'], 400);
            }

            $merchant = $this->merchantService->getAuthenticatedMerchant($token);
            if (!$merchant) {
                \Log::error('Merchant not found');
                return response()->json(['message' => 'Merchant not found'], 404);
            }

            // Retrieve category_type from the query parameter
            $categoryType = $request->query('category_type');

            if (!$categoryType) {
                \Log::error('Category type not provided');
                return response()->json(['message' => 'Category type not provided'], 400);
            }

            $categories = $this->categoryService->getByCategoryType($categoryType, $merchant->id);

            return response()->json([
                'success' => true,
                'message' => 'Categories retrieved successfully',
                'data' => $categories,
            ], 200);
        } catch (\Throwable $th) {
            \Log::error('Error retrieving categories: ' . $th->getMessage());
            return response()->json([
                'error' => 'An unexpected error occurred',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

 public function update(Request $request, $categoryId)
{
    try {
        $token = $request->bearerToken();

        if (!$token) {
            \Log::error('Token not provided');
            return response()->json(['message' => 'Token not provided'], 400);
        }

        $merchant = $this->merchantService->getAuthenticatedMerchant($token);
        if (!$merchant) {
            \Log::error('Merchant not found');
            return response()->json(['message' => 'Merchant not found'], 404);
        }

        // Use the authenticated merchant's ID as the owner_id
        $ownerId = $merchant->id;

        // Validate the incoming data
        $validatedData = $request->validate([
            'category_name' => 'nullable|string',
            'category_type' => 'nullable|string|unique:categories,category_type,NULL,id,owner_id,' . $ownerId,
        ]);

        \Log::info("Validated data: ", $validatedData);

        // Call the service to update the category
        $category = $this->categoryService->updateCategory($categoryId, $validatedData);

        if (!$category) {
            \Log::error('Category not found with ID: ' . $categoryId);
            return response()->json(['message' => 'Category not found'], 404);
        }

        \Log::info('Category updated successfully', ['category_id' => $categoryId]);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => $category,
        ], 200);

    } catch (\Throwable $th) {
        \Log::error('Error updating category: ' . $th->getMessage(), [
            'category_id' => $categoryId,
            'input_data' => $request->all(),
        ]);

        return response()->json([
            'error' => 'An unexpected error occurred',
            'message' => $th->getMessage(),
        ], 500);
    }
}



}
