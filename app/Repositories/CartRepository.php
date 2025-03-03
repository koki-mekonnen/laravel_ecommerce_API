<?php
namespace App\Repositories;

use App\Models\Cart;

class CartRepository
{

    public function addtocart(array $data)
    {
        \Log::info('Data passed to Product::create', $data);
        return Cart::create($data);
    }

    public function findCartItem($productid,$ownerid,$userid){

    return Cart::where('product_id', $productid)->where('owner_id', $ownerid)->where('user_id', $userid)->first();

    }

    public function getCartItems($userid){

     return Cart::where('user_id', $userid)->get();

    }


}
