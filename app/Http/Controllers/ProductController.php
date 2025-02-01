<?php
namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Services\CategoryService;
use App\Services\MerchantService;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    protected $merchantService;
    protected $productService;
    protected $categoryService;

    public function __construct(ProductService $productService, MerchantService $merchantService, CategoryService $categoryService)
    {
        $this->merchantService = $merchantService;
        $this->productService  = $productService;
        $this->categoryService = $categoryService;
    }

    public function store(ProductRequest $request)
    {
        try {
            $token = $request->bearerToken();

            if (! $token) {
                \Log::error('Token not provided');

                return response()->json(['message' => 'Token not provided'], 400);
            }

            $merchant = $this->merchantService->getAuthenticatedMerchant($token);

            if (! $merchant) {
                \Log::error('Merchant not found');

                return response()->json(['message' => 'Merchant not found'], 404);
            }

            $data = $request->validated(); // Get validated data

            $categories = $this->categoryService->getByCategoryName($data['category_name'], $merchant->id);
            if (! $categories) {
                \Log::error('Category not found for name: ' . $data['category_name'] . ' and type: ' . $data['category_type']);
                return response()->json(['message' => 'Category not found'], 404);
            }

            $category = $this->categoryService->getByCategoryType($data['category_type'], $merchant->id);

            if (! $category) {
                \Log::error('Category not found for name: ' . $data['category_name'] . ' and type: ' . $data['category_type']);
                return response()->json(['message' => 'Category not found'], 404);
            }

            \Log::info('Categoryid ' . $category->first()->id);

            $data['category_id'] = $category->first()->id;

            $data['owner_id'] = $merchant->id;

            \Log::info('data passed: ', $data);

            $product = $this->productService->createProduct($data);

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data'    => $product,
            ], 201);
        } catch (\Throwable $e) {
            \Log::error('Error during product creation: ' . $e->getMessage());
            return response()->json([
                'error'   => 'An unexpected error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function index()
    {
        try {
            $token = request()->bearerToken();
            if (! $token) {
                \Log::error('Token not provided');
                return response()->json(['message' => 'Token not provided'], 400);
            }
            $merchant = $this->merchantService->getAuthenticatedMerchant($token);
            if (! $merchant) {
                \Log::error('Merchant not found');
                return response()->json(['message' => 'Merchant not found'], 404);
            }
            $categories = $this->productService->getAllProducts();

            return response()->json([
                'success' => true,
                'message' => 'Products retrieved successfully',
                'data'    => $categories,
            ], 200);
        } catch (\Throwable $e) {
            \Log::error('Error during product retrieval: ' . $e->getMessage());
            return response()->json([
                'error'   => 'An unexpected error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getByProductName(Request $request)
    {
        try {
            $token = $request->bearerToken();

            if (! $token) {
                \Log::error('Token not provided');
                return response()->json(['message' => 'Token not provided'], 400);
            }

            $merchant = $this->merchantService->getAuthenticatedMerchant($token);
            if (! $merchant) {
                \Log::error('Merchant not found');
                return response()->json(['message' => 'Merchant not found'], 404);
            }

            // Retrieve product_name from the query parameter
            $productName = $request->query('product_name');

            if (! $productName) {
                \Log::error('Product name not provided');
                return response()->json(['message' => 'Product name not provided'], 400);
            }

            $categories = $this->productService->getByProductName($productName, $merchant->id);

            return response()->json([
                'success' => true,
                'message' => 'Products retrieved successfully',
                'data'    => $categories,
            ], 200);
        } catch (\Throwable $th) {
            \Log::error('Error retrieving categories: ' . $th->getMessage());
            return response()->json([
                'error'   => 'An unexpected error occurred',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function getByProductType(Request $request)
    {
        try {
            $token = $request->bearerToken();

            if (! $token) {
                \Log::error('Token not provided');
                return response()->json(['message' => 'Token not provided'], 400);
            }

            $merchant = $this->merchantService->getAuthenticatedMerchant($token);
            if (! $merchant) {
                \Log::error('Merchant not found');
                return response()->json(['message' => 'Merchant not found'], 404);
            }

            // Retrieve product_type from the query parameter
            $productType = $request->query('product_type');

            if (! $productType) {
                \Log::error('Product type not provided');
                return response()->json(['message' => 'Product type not provided'], 400);
            }

            $categories = $this->productService->getByProductType($productType, $merchant->id);

            return response()->json([
                'success' => true,
                'message' => 'Products retrieved successfully',
                'data'    => $categories,
            ], 200);
        } catch (\Throwable $th) {
            \Log::error('Error retrieving categories: ' . $th->getMessage());
            return response()->json([
                'error'   => 'An unexpected error occurred',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $productId)
    {
        try {
            $token = $request->bearerToken();

            if (! $token) {
                \Log::error('Token not provided');
                return response()->json(['message' => 'Token not provided'], 400);
            }

            $merchant = $this->merchantService->getAuthenticatedMerchant($token);
            if (! $merchant) {
                \Log::error('Merchant not found');
                return response()->json(['message' => 'Merchant not found'], 404);
            }

            // Use the authenticated merchant's ID as the owner_id
            $ownerId = $merchant->id;

            // Validate the incoming data
            $validatedData = $request->validate([
                'product_name' => 'nullable|string',
                'product_type' => 'nullable|string|unique:categories,product_type,NULL,id,owner_id,' . $ownerId,
            ]);

            \Log::info("Validated data: ", $validatedData);

            // Call the service to update the product
            $product = $this->productService->updateProduct($productId, $validatedData);

            if (! $product) {
                \Log::error('Product not found with ID: ' . $productId);
                return response()->json(['message' => 'Product not found'], 404);
            }

            \Log::info('Product updated successfully', ['product_id' => $productId]);

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data'    => $product,
            ], 200);

        } catch (\Throwable $th) {
            \Log::error('Error updating product: ' . $th->getMessage(), [
                'product_id' => $productId,
                'input_data' => $request->all(),
            ]);

            return response()->json([
                'error'   => 'An unexpected error occurred',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function delete(Request $request, $productId)
    {
        try {
            $token = $request->bearerToken();

            if (! $token) {
                \Log::error('Token not provided');
                return response()->json(['message' => 'Token not provided'], 400);
            }

            $merchant = $this->merchantService->getAuthenticatedMerchant($token);
            if (! $merchant) {
                \Log::error('Merchant not found');
                return response()->json(['message' => 'Merchant not found'], 404);
            }

            // Use the authenticated merchant's ID as the owner_id
            $ownerId = $merchant->id;

            $product = $this->productService->deleteProduct($productId);

            if (!$product) {
                \Log::error('Product not found with ID: ' . $productId);
                return response()->json(['message' => 'Product not found'], 404);
            }

            \Log::info('Product deleted successfully', ['product_id' => $productId]);

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully',
                'data'    => $product,
            ], 200);

        } catch (\Throwable $th) {
            \Log::error('Error delteting product: ' . $th->getMessage(), [
                'product_id' => $productId,
                'input_data' => $request->all(),
            ]);

            return response()->json([
                'error'   => 'An unexpected error occurred',
                'message' => $th->getMessage(),
            ], 500);
        }

    }

}
