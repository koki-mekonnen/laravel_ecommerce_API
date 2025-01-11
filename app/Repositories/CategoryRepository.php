<?php

namespace App\Repositories;

use App\Models\Category;

class CategoryRepository
{
    // public function findByEmailAndRole($email, $role)
    // {
    //     return Category::where('email', $email)->where('role', $role)->first();
    // }

    // public function findByPhoneAndRole($phone, $role)
    // {
    //     return Category::where('phone', $phone)
    //         ->where('role', $role)
    //         ->first();
    // }

    public function create(array $data)
    {
        return Category::create($data);
    }

    public function findById($id)
    {
        return Category::find($id);
    }
    public function all()
    {
        return Category::all();
    }

    public function getByCategoryType($categorytype, $merchantId)
    {
        return Category::where('category_type', $categorytype)
            ->where('owner_id', $merchantId)
            ->get();

    }

    public function getByCategoryName($categoryName, $merchantId)
    {
        return Category::where('category_name', $categoryName)
            ->where('owner_id', $merchantId)
            ->get();
    }

    public function update (array $data,$id)
{
    return Category::where('id', $id)->update($data);

}



    public function delete($id)
    {
        return Category::destroy($id);
    }
}
