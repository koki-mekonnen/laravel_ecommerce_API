<?php
namespace App\Repositories;

use App\Models\User;
use App\Models\Category;
use App\Models\Merchant;
use App\Models\Product;


class UserRepository
{
    public function findByEmailAndRole($email, $role)
    {
        return User::where('email', $email)->where('role', $role)->first();
    }

    public function findByPhoneAndRole($phone, $role)
    {
        return User::where('phone', $phone)
            ->where('role', $role)
            ->first();
    }

    public function create(array $data)
    {
        return User::create($data);
    }

    public function findById($id)
    {
        return User::find($id);
    }

  public function update(array $data, $id)
{
    $user = User::findOrFail($id);
    $user->update($data);
    return $user;
}

 public static function getMerchantsByCategoryName($categoryName)
    {
        $ownerIds = Category::where('category_name', $categoryName)
                        ->pluck('owner_id')
                        ->unique()
                        ->toArray();

        return Merchant::whereIn('id', $ownerIds)->get();
    }

    public function allCategories()
    {
        return Category::all();
    }


    public function getProductsByCategoryName($categoryName, $ownerId){
        return Product::where('category_name', $categoryName)
            ->where('owner_id', $ownerId)
            ->get();
    }

   


}
