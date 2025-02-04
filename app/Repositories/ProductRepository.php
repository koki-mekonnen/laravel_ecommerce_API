<?php
namespace App\Repositories;

use App\Models\Product;

class ProductRepository
{
    // public function findByEmailAndRole($email, $role)
    // {
    //     return Product::where('email', $email)->where('role', $role)->first();
    // }

    // public function findByPhoneAndRole($phone, $role)
    // {
    //     return Product::where('phone', $phone)
    //         ->where('role', $role)
    //         ->first();
    // }

    public function create(array $data)
    {
        \Log::info('Data passed to Product::create', $data);
        return Product::create($data);
    }

    public function findById($id)
    {
        return Product::find($id);
    }
    public function all()
    {
        return Product::all();
    }

    public function findWhere(array $data)
    {
        $query = Product::query();

        foreach ($data as $field => $value) {
            if (! empty($value)) {
                if (is_numeric($value)) {
                    $query->where($field, $value);
                } elseif (is_string($value)) {
                    $query->whereRaw("LOWER($field) = LOWER(?)", [$value]);
                } else {
                    $query->where($field, $value);
                }
            }
        }

        $existingProduct = $query->first();

        \Log::info('Existing Product:', ['data' => $existingProduct]);

        return $existingProduct;
    }

    public function getByProductType($producttype, $merchantId)
    {
        return Product::where('product_type', $producttype)
            ->where('owner_id', $merchantId)
            ->get();

    }

    public function getByProductName($productName, $merchantId)
    {
        return Product::where('product_name', $productName)
            ->where('owner_id', $merchantId)
            ->get();
    }

    public function update(array $data, $id)
    {
        $product = Product::find($id);

        if (! $product) {
            throw new \Exception('Product not found.');
        }

        $product->update($data);

        return $product;

    }

    public function delete($id)
    {
        return Product::destroy($id);
    }
}
