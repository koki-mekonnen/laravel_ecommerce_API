<?php
namespace App\Services;

use App\Repositories\ProductRepository;

class ProductService
{
    protected $repository;

    public function __construct(ProductRepository $repository)
    {
        $this->repository = $repository;
    }

    public function createProduct(array $data)
    {
        // Log incoming data
        \Log::info('Register Product data received', $data);

        try {

            $existingProduct = $this->repository->findWhere($data);

            if ($existingProduct) {
                \Log::warning('Product already exists', [
                    'id'           => $existingProduct->id,
                    'product_name' => $existingProduct->product_name,
                    'product_type' => $existingProduct->product_type,
                ]);

                throw new \Exception('Product already exists in the database.');
            }

            $product = $this->repository->create($data);

            \Log::info('Product successfully created', [
                'id'           => $product->id,
                'product_name' => $product->product_name,
                'product_type' => $product->product_type,
            ]);

            return $product;
        } catch (\Exception $e) {
            // Log the error message
            \Log::error('Error registering product: ' . $e->getMessage());

            // Re-throw the exception to be caught in the controller
            throw $e;
        }
    }

    public function getAllProducts()
    {
        try {
            \Log::info('Getting all products');
            $products = $this->repository->all();
            return $products;
        } catch (\Exception $e) {
            \Log::error('Error getting all products: ' . $e->getMessage());
            throw $e;
        }
    }


   public function getById($id){
    try {
        \Log::info('Getting product by ID: ' . $id);
        $product = $this->repository->findById($id);

        if (!$product) {
            \Log::warning('Product not found with ID: ' . $id);
            return response()->json(['message' => 'Product not found'], 404);
        }

        \Log::info('Product found: ' . json_encode($product));

        return $product;
    } catch (\Exception $e) {
        \Log::error('Error getting product by ID: '. $id . ': ' . $e->getMessage());
        throw $e;
    }
}


    public function getByProductName($productName, $merchantid)
    {
        try {
            \Log::info('Getting product by name: ' . $productName);
            $product = $this->repository->getByProductName($productName, $merchantid);
            if (! $product) {
                \Log::info('Product not found with name: ' . $productName);
                return response()->json(['message' => 'Product not found'], 404);
            }
            return $product;
        } catch (\Exception $e) {
            \Log::error('Error getting product by name: ' . $productName . ': ' . $e->getMessage());
            throw $e;
        }
    }

    public function getByProductType($productType, $merchantid)
    {
        try {
            \Log::info('Getting product by type: ' . $productType);
            $product = $this->repository->getByProductType($productType, $merchantid);
            if (! $product) {
                \Log::info('Product not found with type: ' . $productType);
                return response()->json(['message' => 'Product not found'], 404);
            }
            return $product;
        } catch (\Exception $e) {
            \Log::error('Error getting product by name: ' . $productType . ': ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateProduct($id, array $data)
    {
        try {

            \Log::info('Updating product with ID: ' . $id . ' and data: ', $data);

            $product = $this->repository->findById($id);

            if (! $product) {
                \Log::info('Product not found with ID: ' . $id);
                return response()->json(['message' => 'Product not found'], 404);
            }

            $product->update($data);

            $product = $this->repository->findById($id);

            \Log::info('Product updated successfully: ', ['id' => $product->id]);

            return response()->json($product, 200);

        } catch (\Exception $e) {

            \Log::error('Error updating product with ID: ' . $id . '. Exception: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while updating the product.'], 500);

        }
    }

    public function deleteProduct($productId)
    {
        try {
            \Log::info('Deleting product with ID: ' . $productId);
            $product = $this->repository->findById($productId);
            if (! $product) {
                \Log::info('Product not found with ID: ' . $productId);
                return response()->json(['message' => 'Product not found'], 404);
            }
            $product = $this->repository->delete($productId);

            \Log::info('Product deleted successfully: ' . $productId);
            return response()->json(['message' => 'Product deleted successfully'], 204);
        } catch (\Exception $e) {
            \Log::error('Error deleting product: ' . $productId . ': ' . $e->getMessage());
            throw $e;
        }
    }
}
