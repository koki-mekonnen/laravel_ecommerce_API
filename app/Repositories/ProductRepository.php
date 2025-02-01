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
        return Product::where('id', $id)->update($data);

    }

    public function delete($id)
    {
        return Product::destroy($id);
    }
}
